<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\CityModel;
use Illuminate\Support\Facades\Mail;
use App\Mail\testMailClass;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Bridge\AccessToken;
use App\Http\Controllers\mailController;
use App\Http\Controllers\userController;





class LoginController extends Controller
{

/** 
    * @OA\Post(
    *     path="/api/auth/registr",
    *     summary="Регистрация",
    *     tags={"Auth"},
    *     @OA\RequestBody(
    *     required=true,
    *     description="Pass user credentials",
    *     @OA\JsonContent(
    *       required={"name","email","password","phone_number","id_city"},
    *       @OA\Property(property="name", type="string", example="Андрей"),
    *       @OA\Property(property="email", type="string",example="user@mail.com"),
    *       @OA\Property(property="password", type="string",example="!User123456"),
    *       @OA\Property(property="phone_number", type="string",example="+7(123)4567890"),
    *       @OA\Property(property="id_city", type="int", example="1"),
    *    ),
    * ),
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="Validation error",
    *     ),
    * ),
    */

    public function registr(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|between:2,100',
            'email' => 'required|email|unique:users|max:50',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }

        $id_city = $request->input('id_city');
        $user = User::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                'id_city' => $id_city,
            ],

        ));
        $user->save();


        return response()->json([
            'message' => 'Successfully registered',
            'user' => $user
        ], 200);
    }

/** 
    * @OA\Post(
    *     path="/api/auth/login",
    *     summary="Авторизация",
    *     tags={"Auth"},
    *     @OA\RequestBody(
    *     required=true,
    *     description="Pass user credentials",
    *     @OA\JsonContent(
    *       required={"email","password"},
    *       @OA\Property(property="email", type="string",example="user@mail.com"),
    *       @OA\Property(property="password", type="string",example="!User123456"),
    *    ),
    * ),
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="Validation error",
    *     ),
    *     @OA\Response(
    *         response="401",
    *         description="Unauthorized",
    *     ),
    * ),
*/
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $response = Http::asForm()->post((env('APP_DEV_URL') . '/oauth/token'), [
            'grant_type' => 'password',
            'client_id' => env('PASSWORD_CLIENT_ID'),
            'client_secret' => env('PERSONAL_CLIENT_SECRET'),
            'username' => $request->get('email'),
            'password' => $request->get('password'),
            'scope' => '',
        ]);

        return response()->json($response->json());
    }

/** 
    * @OA\Get(
    *     path="/api/auth/user-profile",
    *     summary="Получить данные пользователя",
    *     tags={"Auth"},
    *     security={
    *           {"passport": {}},
    *      },
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    *     @OA\Response(
    *         response="401",
    *         description="Unauthorized",
    *     ),
    * ),
*/
    public function profile()
    {
        return (new userController())->userInfo(auth('api')->user());
    }


/** 
    * @OA\Post(
    *     path="/api/auth/refresh",
    *     summary="Обновление refresh токена",
    *     tags={"Auth"},
    *     @OA\Parameter(
    *       name="refresh_token",
    *       in="query",
    *       required=true,
    *       ), 
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    * )
*/
    public function refresh(Request $request)
    {
        $response = Http::asForm()->post(env('APP_DEV_URL') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->get('refresh_token'),
            'client_id' => env('PASSWORD_CLIENT_ID'),
            'client_secret' => env('PERSONAL_CLIENT_SECRET'),
            'scope' => '',
        ]);

        return $response->json();
    }
}
