<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CityModel;
class userController extends Controller
{
    public function userInfo(User $user)
    {
        return response()->json([
            'id'=> $user->id,
            'email'=> $user->email ,
            'name' => $user->name,
            'email_verified_at'=> $user->email_verified_at,
            'phone_number'=> $user->phone_number,
            'blocked_admin'=> $user->blocked_admin,
            'num_login_attempts'=> $user->num_login_attempts,
            'is_admin'=> $user->is_admin,
            'city'=> CityModel::find($user->id_city)->name,
            ]);
    }
}
