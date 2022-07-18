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

    public function sentMail(Request $request)
    {

        $this->id = $request->input('id');
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
        $this->id = $request->input('id');
        $this -> user = User::find($this->id);
        if($this->user->email_code == $request->input('email_code'))
        {
            $this -> user -> email_verified_at = now();
            $this -> user->save();
            return 'WELL';

        }
        else
        {
            return 'FUCK YOU!';
        }
    }
}
