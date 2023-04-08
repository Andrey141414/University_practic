<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\testMailClass;
use App\Models\User;
use App\Models\CityModel;
use App\Http\Controllers\Auth\LoginController;

class mailController extends Controller
{
    public int $id;
    public User $user;


/**
     * @OA\Get(
     *     path="/api/sent_code",
     *     summary="Отправляет код на почту",
     *     tags={"Mail"},
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
     *         description="Unauthorized user",
     *     ),
     * )
*/
    public function sentMail()
    {


        $this->id = auth('api')->user()->id;
        $this->user = User::find($this->id);

        if ($this->user == null) {
            return 'There is no so user';
        }

        $code = rand(1000, 9999);
        Mail::to($this->user)->send(new testMailClass($code));
        $this->user->email_code = $code;
        $this->user->save();
        return $this->user->email;
    }


/**
     * @OA\Post(
     *     path="/api/check_code",
     *     summary="Проверка кода, который пришел на почту",
     *     tags={"Mail"},
     *     security={
     *           {"passport": {}},
     *      },
     *     @OA\RequestBody(
    *     required=true,
    *     description="Pass user credentials",
     *      @OA\JsonContent(
     *       required={"email_code"},
     *       @OA\Property(property="email_code", type="int", example="1234"),
     *    ),),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     *     @OA\Response(
     *         response="402",
     *         description="Incorrect code",
     *     ),
     * )
*/
    public function checkMail(Request $request)
    {

        $this->id = auth('api')->user()->id;
        $this->user = User::find($this->id);
        if ($this->user->email_code == $request->input('email_code')) {
            $this->user->email_verified_at = now();
            $this->user->save();

            return (new userController())->userInfo(auth('api')->user());
        } else {
            return response()->json([
                'message' => 'Incorrect code'
            ], 402);
        }
    }
}
