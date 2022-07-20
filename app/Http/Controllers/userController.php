<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CityModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\postModel;

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


    public function test()
    {
        // $arr = [];
        // $arr[0] = 1;
        // $arr[1] = 4;
        $id = 18;
        $id_post = 15;
        $post = (new postModel())->where('id',$id_post)->first();
        $post->img_set_path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post;
        
        $post->save();
        return response()->json($post);
    }
}
