<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\testMailClass;
use App\Mode\USer;

class mailController extends Controller
{
    public function sentMail()
    {
        $code = rand(1000, 9999);
        //$user = new User();
        Mail::to('andrusha.vinokurov@gmail.com')->send(new testMailClass($code));
    }
}
