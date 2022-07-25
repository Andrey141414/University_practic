<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\resentPassword;
use Auth;
use Hash;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;


class passwordController extends Controller
{
    public function sendPasswordResetToken(Request $request)
    {
        $user = User::where ('email', $request->email)->first();
        if ( !$user )return response()->json([
            'message' => 'There is no so user'
        ], 200);
    
        //create a new token to be sent to the user. 
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Str::random(60), //change 60 to any length you want
            'created_at' => Carbon::now()
        ]);
    
        $tokenData = DB::table('password_resets')
        ->where('email', $request->email)->latest()->first();
    
       $token = $tokenData->token;
       $email = $request->email; // or $email = $tokenData->email;
       Mail::to($email)->send(new resentPassword($token));
       return response()->json($token,200);
       /**
        * Send email to the email above with a link to your password reset
        * something like url('password-reset/' . $token)
        * Sending email varies according to your Laravel version. Very easy to implement
        */
    }



    /**
 * Assuming the URL looks like this 
 * http://localhost/password-reset/random-string-here
 * You check if the user and the token exist and display a page
 */

 public function isshowPasswordResetForm(Request $request)
 {
     $token = $request->input('token');
     $tokenData = DB::table('password_resets')
     ->where('token', $token)->first();

     if ( !$tokenData )return response()->json(['isValid'=>false],400); //redirect them anywhere you want if the token does not exist.
     
     return response()->json(['isValid'=>true],200);
 }



 public function resetPassword(Request $request)
 {
     //some validation
     $password = $request->input('password');

     $tokenData = DB::table('password_resets')
     ->where('token', $request->input('token'))->first();

     $user = User::where('email', $tokenData->email)->first();
     if ( !$user )  return response()->json([
        'message' => 'User Error'
    ], 400); //or wherever you want

     $user->password = bcrypt($password);
     $user->update(); //or $user->save();

     //do we log the user directly or let them login and try their password for the first time ? if yes 
     //auth::login($user);

    // If the user shouldn't reuse the token later, delete the token 
    DB::table('password_resets')->where('email', $user->email)->delete();
    
    return response()->json('password changed');
    //redirect where we want according to whether they are logged in or not.
 }


}
