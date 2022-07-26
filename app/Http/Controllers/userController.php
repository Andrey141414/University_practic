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


    public function test(Request $request)
    {
        // // $arr = [];
        // // $arr[0] = 1;
        // // $arr[1] = 4;
        // $id = 18;
        // $id_post = 15;
        // $post = (new postModel())->where('id',$id_post)->first();
        // $post->img_set_path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post;
        //(new postModel())->where('title', null)->delete();
        
        //Kernel
        

        // $posts = (new postModel())::all();
        // foreach($posts as $post)
        // {

        //     $path = 'IN_GOOD_HANDS/'.$post->id_user.'/'.$post->id;
        //     $content = Storage::disk("google")->get($path.'/0.jpeg');
        //     Storage::disk("local")->makeDirectory($path);
        //     Storage::disk("local")->put($path.'/0.jpeg',$content);

        // } 

        //Storage::disk("local")->deleteDirectory('IN_GOOD_HANDS');
        // $path = 'IN_GOOD_HANDS/12/111';
        // $a = Storage::disk("local")->exists($path.'/example.txt');
        
        return count(Storage::disk("google")->allFiles('\IN_GOOD_HANDS\18\163'));

        // $items_num = 3;
        // $posts = new postModel();
        // $previews = array();

        // for($i = 0;$i<$items_num;$i++)
        // {
        //     $path = $posts->simplePaginate($items_num)->items()[$i]->img_set_path;
        //     array_push($previews,base64_encode(Storage::disk("google")->get($path.'/0.jpeg')));
        // }

        
        
        // return [$path = $posts->simplePaginate($items_num),( $previews)];

        // $user_posts = (new postModel())->where('id_user',18)->get();
        // return (new postModel())->where('id_user',18)->simplePaginate(4)->items()[2]->img_set_path;



        $previews = array();
        $previews  = (new postModel())->pluck('img_set_path')->toArray();
        return $previews;
    }
}
