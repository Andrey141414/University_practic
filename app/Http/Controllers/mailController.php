<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\testMailClass;
class mailController extends Controller
{
    public function sentMail()
    {
        Mail::to('pi91836@yandex.ru')->send(new testMailClass());
    }
}
