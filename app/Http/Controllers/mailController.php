<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\testMailClass;
use App\Models\User;

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
            return response()->json($this->user);

        }
        else
        {
            return response()->json([
                'message' => 'Incorrect code'
            ], 401);
        }
    }
}
