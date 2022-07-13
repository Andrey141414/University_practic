<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\LoginProxy;
use Illuminate\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    

    public function token(Request $request)
    {
        $response = Http::asForm()->post('http://127.0.0.1:8000/oauth/token' , [
            'grant_type' => 'password',
            'client_id' => 2,
            'client_secret' => env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET'),//'fgRE04VilkM77asl4298NO9mFusbbWHyCAHi0kBb',
            'username' => $request->get('email'),
            'password' => $request->get('password'),
            'scope' => '',
        ]);

        return $response->json();
    }

    public function refresh(Request $request)
    {
        $response = Http::asForm()->post(config('app.url').'/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->get('refresh_token'),
            'client_id' => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'scope' => '',
        ]);

        return $response->json();
    }
}