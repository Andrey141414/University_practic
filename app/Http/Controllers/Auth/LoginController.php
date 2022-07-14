<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    

    public function register(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
                    'name' => 'required|between:2,100',
                    'email' => 'required|email|unique:users|max:50',
                    'password' => 'required|string|min:6',
                ]);
        
    $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
    ));

    return response()->json([
        'message' => 'Successfully registered',
        'user' => $user
    ], 201);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 404);
        }
        
        $response = Http::asForm()->post(('https://polar-eyrie-91847.herokuapp.com/oauth/token'),[
                'grant_type' => 'password',
                'client_id' => env('PASSWORD_CLIENT_ID'),
                'client_secret' => env('PERSONAL_CLIENT_SECRET'),
                'username' => $request->get('email'),
                'password' => $request->get('password'),
                'scope' => '',
        ]); 
        return response()->json($response->json());
        }
    
    
    public function profile()
    {
        if(auth('api')->user() != null){    
        return response()->json(auth('api')->user());
        }
        else
        {
            return response()->json([
                'message' => 'Unauthorized'
            ], 404);
        }
    }

    
    public function refresh(Request $request)
    {
        $response = Http::asForm()->post('https://polar-eyrie-91847.herokuapp.com/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->get('refresh_token'),
            'client_id' => '2',
            'client_secret' => 'fgRE04VilkM77asl4298NO9mFusbbWHyCAHi0kBb',
            'scope' => '',
        ]);

        return $response->json();
    }
}