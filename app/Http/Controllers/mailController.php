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

    public function sentMail()
    {

        if(auth('api')->user() == null)
        {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $this->id = auth('api')->user()->id;
        $this -> user = User::find($this->id);
    
        if($this -> user == null)
        {
            return 'There is no so user';
        }

        $code = rand(1000, 9999);
        Mail::to($this->user)->send(new testMailClass($code));
        $this -> user->email_code = $code;
        $this -> user->save();
        return $this -> user->email;
    }

    public function checkMail(Request $request)
    {
        if(auth('api')->user() == null)
        {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $this->id = auth('api')->user()->id;
        $this -> user = User::find($this->id);
        if($this->user->email_code == $request->input('email_code'))
        {
            $this -> user -> email_verified_at = now();
            $this -> user->save();

                return response()->json([
        'email'=> auth('api')->user()->email ,
        'name' => auth('api')->user()->name,
        'email_verified_at'=> auth('api')->user()->email_verified_at,
        'phone_number'=> auth('api')->user()->phone_number,
        'blocked_admin'=> auth('api')->user()->blocked_admin,
        'num_login_attempts'=> auth('api')->user()->num_login_attempts,
        'is_admin'=> auth('api')->user()->is_admin,
        'city'=> CityModel::find(auth('api')->user()->id_city)->name,
        ]);
        }
        else
        {
            return response()->json([
                'message' => 'Incorrect code'
            ], 401);
        }
    }
}
