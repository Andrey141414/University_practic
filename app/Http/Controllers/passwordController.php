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
        if ( !$user ) return redirect()->back()->withErrors(['error' => '404']);
    
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
       return response()->json($token);
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

 public function showPasswordResetForm(Request $request)
 {
    return 1234;
     $token = $request->get('token');
     $tokenData = DB::table('password_resets')
     ->where('token', $token)->first();

    return $token;
     if ( !$tokenData )redirect('http://46.50.152.115:3000/reset-password'); //redirect them anywhere you want if the token does not exist.
     
     else 
     return response()->json('well',200);
 }



 public function resetPassword(Request $request)
 {
     //some validation
     $password = $request->input('password');
     $tokenData = $request->input('token');
    //  $tokenData = DB::table('password_resets')
    //  ->where('token', $token)->first();

     $user = User::where('email', $tokenData->email)->first();
     //if ( !$user ) return redirect()->to('home'); //or wherever you want

     $user->password = bcrypt($password);
     $user->update(); //or $user->save();

     //do we log the user directly or let them login and try their password for the first time ? if yes 
     //auth::login($user);

    // If the user shouldn't reuse the token later, delete the token 
    DB::table('password_resets')->where('email', $user->email)->delete();

    //redirect where we want according to whether they are logged in or not.
 }


}
