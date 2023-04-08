<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CityModel;
use Illuminate\Support\Facades\Storage;
use App\Models\AddressModel;
use App\Models\reviewModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Validator;
use App\Service\UserService;

class userController extends Controller
{
    public function userInfo(User $user)
    {

        //$rating = 0;

       
        // foreach ($reviews as $review) {
        //     $rating += $review->score;
        // }

        // if(count($reviews) == 0)
        // {
        //     $rating = 0;
        // }
        // else
        // {
        //     $rating /= count($reviews);
        // } 
        $reviews = reviewModel::where('id_user_owner', $user->id)->get();
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'email_verified_at' => isset($user->email_verified_at) ? date('d-m-Y',strtotime($user->email_verified_at)) : null,
            'phone_number' => $user->phone_number,
            'blocked_admin' => $user->blocked_admin,
            'num_login_attempts' => $user->num_login_attempts,
            'is_admin' => $user->is_admin,
            'id_city' => CityModel::find($user->id_city)->id,
            'addresses' => (AddressModel::where('id_user', $user->id))->get(),
            'created_at' => date('d-m-Y', strtotime($user->created_at)),
            'rating' => UserService::calculateRating($user->id),
            'reviews' => count($reviews),
        ]);
    }


    public function deleteAccount(Request $request)
    {
        $id_user = auth('api')->user()->id;

        $user = User::where('id', $id_user)->first();

        Storage::disk("local")->delete('public/IN_GOOD_HANDS/' . $id_user);
        //Storage::disk("google")->delete('IN_GOOD_HANDS/'.$id_user);
        $user->delete();

        AddressModel::where('id_user', $id_user)->delete();
        return response()->json('account was deleted', 200);
    }

    public function test1()
    {
        //print_r(Storage::allDirectories('/public/IN_GOOD_HANDS'));
        return Storage::allDirectories('/public/IN_GOOD_HANDS');
    }




    public function test(Request $request)
    {

//         $json = file_get_contents('http://maps.google.com/maps/nav?q=from:Buderim,Australia%20to:Brisbane,Australia');

// $details = json_decode($json, TRUE);

// var_dump($details['Directions']['Distance']['meters']);

// print_r(Storage::allDirectories('/public/IN_GOOD_HANDS'));

       //Storage::disk('local')->deleteDirectory('PHOTOS8');
        //print_r(Storage::disk('public')->allDirectories('/'));
               
        // foreach(Storage::disk('local')->allDirectories('/public') as $folder)
        // {
        //     Storage::disk('local')->deleteDirectory($folder);
        // }
        
        //echo(json_encode() );

        //return;
        //'{"title":"\u0433 \u0411\u0430\u0440\u043d\u0430\u0443\u043b, \u041a\u0440\u0430\u0441\u043d\u043e\u0430\u0440\u043c\u0435\u0439\u0441\u043a\u0438\u0439 \u043f\u0440-\u043a\u0442, \u0434 3","longitude":"83,78349","latitude":"53,32353"}';
        

        $address = $request->input('address');
        

        
        // $validator = Validator::make($request->all(),[
        //     'address' => 'required|size:3',
        // ]);
        
        // if ($validator->fails()) {
        //     return response()->json([
        //         'message' => 'Validation error'
        //     ], 400);
        // }
        

        //return count($address);
        
        $validator = Validator::make($address,[
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }

        $address['longitude'] = (double)($address['longitude']);

        return $address;
        //Storage::disk("local")->makeDirectory('public/'.'IN_GOOD_HANDS/'.$id.'/'.$id_post);

        //цикл
        //Storage::disk("local")->makeDirectory('123',"0777");

        // foreach(Storage::disk("local")->allDirectories() as $dir)
        // {
        //     if(Storage::disk("local")->allFiles())
        // }

        //$response = Storage::disk("local")->deleteDirectory('public/TEST');

        //$response = Storage::disk("local")->allDirectories();
        $response = Storage::disk("local")->allFiles('public/IN_GOOD_HANDS/6/16');

        // $path = '123'.'/1.jpeg';
        // $data = base64_decode($request->input('data'));
        // Storage::disk("local")->put($path,$data);

        //конец цикла
        return $response;
    }
}



    //$items_num = 10;
    //     $posts = (new postModel())::all();
    //     //return $posts;
    //     $address = [];
    //     for($i = 0;$i<10;$i++ )
    //     {
    //     $address[$i] = AddressModel::where('id',$posts->paginate($items_num)->items()[$i]->id_address)->first();
    //     }
    //     return $address;
    //     return [Storage::disk("local")->allDirectories(),Storage::disk("local")->allFiles(),201];
    //     // // $arr = [];
    //     // // $arr[0] = 1;
    //     // // $arr[1] = 4;
    //     // $id = 18;
    //     // $id_post = 15;
    //     // $post = (new postModel())->where('id',$id_post)->first();
    //     // $post->img_set_path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post;
    //     //(new postModel())->where('title', null)->delete();
        
    //     //Kernel
        

    //     // $posts = (new postModel())::all();
    //     // foreach($posts as $post)
    //     // {

    //     //     $path = 'IN_GOOD_HANDS/'.$post->id_user.'/'.$post->id;
    //     //     $content = Storage::disk("google")->get($path.'/0.jpeg');
    //     //     Storage::disk("local")->makeDirectory($path);
    //     //     Storage::disk("local")->put($path.'/0.jpeg',$content);

    //     // } 

    //     //Storage::disk("local")->deleteDirectory('IN_GOOD_HANDS');
    //     // $path = 'IN_GOOD_HANDS/12/111';
    //     // $a = Storage::disk("local")->exists($path.'/example.txt');
        

    // //$pool = new DefaultPool(8);

 

    // // $pageSources = collect($urls)->parallelMap(function ($url) {
    // // return file_get_contents($url);
    // // });
    //     return Storage::url('\IN_GOOD_HANDS\81\162\0.jpeg');

    //     // $items_num = 3;
    //     // $posts = new postModel();
    //     // $previews = array();

    //     // for($i = 0;$i<$items_num;$i++)
    //     // {
    //     //     $path = $posts->simplePaginate($items_num)->items()[$i]->img_set_path;
    //     //     array_push($previews,base64_encode(Storage::disk("google")->get($path.'/0.jpeg')));
    //     // }

        
        
    //     // return [$path = $posts->simplePaginate($items_num),( $previews)];

    //     // $user_posts = (new postModel())->where('id_user',18)->get();
    //     // return (new postModel())->where('id_user',18)->simplePaginate(4)->items()[2]->img_set_path;



    //     $previews = array();
    //     $previews  = (new postModel())->pluck('img_set_path')->toArray();
    //     return $previews;