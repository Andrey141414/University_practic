<?php
namespace App\Http\Controllers\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\accessTokens;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
 //
 public function getIdByAccessToken(Request $request) {

   $token = $request->bearerToken();
   $id = accessTokens::where('id',$token)->user_id;
   return $id;//User::where('id','like',$id);
}

}