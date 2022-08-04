<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CityModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\postModel;
use Amp\Parallel\Worker\DefaultPool;
use App\Models\AddressModel;
use Faker\Provider\ar_EG\Address;

use function Amp\ParallelFunctions\parallelMap;
use function Amp\Promise\wait;

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
            'id_city'=> CityModel::find($user->id_city)->id,
            'addresses'=> (AddressModel::where('id_user',$user->id))->get()
            ]);
    }


    public function deleteAccount(Request $request)
    {
        $id_user = auth('api')->user()->id;

        $user = User::where('id',$id_user)->first();

        Storage::disk("local")->delete('public/IN_GOOD_HANDS/'.$id_user);
        Storage::disk("google")->delete('IN_GOOD_HANDS/'.$id_user);
        $user->delete();

        AddressModel::where('id_user',$id_user)->delete();
        return response()->json('account was deleted',200);
        
    } 

    public function test(Request $request)
    {

        return [Storage::disk("local")->allDirectories(),Storage::disk("local")->allFiles(),201];
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
        

    //$pool = new DefaultPool(8);

 

    // $pageSources = collect($urls)->parallelMap(function ($url) {
    // return file_get_contents($url);
    // });
        return Storage::url('\IN_GOOD_HANDS\81\162\0.jpeg');

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
