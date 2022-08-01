<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;


use Illuminate\Http\File;

class postController extends Controller
{
    public function createPost(Request $request)
    {
        $id = auth('api')->user()->id;
        
        $post = (new postModel());
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:1,50',
            //'description' => 'max:300',
            'id_category' => 'required',
            'image_set' => 'required|between:1,5',
            'id_city' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }


        $post->title = $request->input('title');
        $post->description = $request->input('description');
        $post->date = Carbon::now();
        $post->id_category = $request->input('id_category');
        $post-> id_user = $id;
        $post-> id_city = $request->input('id_city');
        
        $post->save();
        $id_post =  $post->id;
  
        $images = $request->input('image_set');
        
        
        Storage::disk("google")->makeDirectory('IN_GOOD_HANDS/'.$id.'/'.$id_post);

        Storage::disk("local")->makeDirectory('public/'.'IN_GOOD_HANDS/'.$id.'/'.$id_post);
        
        //цикл
        foreach ($images as $key => $data) {
            $path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post.'/'.$key.'.jpeg';
            $data = base64_decode($data);
            Storage::disk("google")->put($path,$data);

            Storage::disk("local")->put('public/'.$path,$data);
        }
        //конец цикла
        
        $post->img_set_path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post;
        $post->save();
        return response()->json(["message"=>"Data was saved"],200);
    }

    public function deletePost(Request $request)
    {
        $id_post = $request->get('id_post');
        $post = (new postModel())->where('id',$id_post)->first();
        if($post==null)
        {
            return response()->json([
                "message" => "post is missing"
            ], 404);
        }

        $path = $post->img_set_path;
        $post->delete();
        Storage::disk("google")->deleteDirectory($path);
        Storage::disk("local")->deleteDirectory('public/'.$path);
        return response()->json(["message"=>"Data was deleted"],200);

    }
    public function changePost(Request $request)
    {
        $id = auth('api')->user()->id;
        $id_post = $request->input('id_post');
        $post = (new postModel())->where('id',$id_post)->first();
        if($post==null)
        {
            return response()->json([
                "message" => "post is missing"
            ], 404);
        }
        $post->title = $request->input('title');
        $post->description = $request->input('description');
        $post->id_category = $request->input('id_category');

        
        Storage::disk("google")->deleteDirectory($post->img_set_path);

        $images = $request->input('image_set');
        Storage::disk("google")->makeDirectory('IN_GOOD_HANDS/'.$id.'/'.$id_post);
        Storage::disk("local")->makeDirectory('public/'.'IN_GOOD_HANDS/'.$id.'/'.$id_post);

        //цикл
        foreach ($images as $key => $data) {
            $path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post.'/'.$key.'.jpeg';
            $data = base64_decode($data);
            Storage::disk("google")->put($path,$data);
            Storage::disk("local")->put('public/'.$path,$data);
        }
        //конец цикла
        
        $post->img_set_path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post;
        $post->save();
        return response()->json(["message"=>"Data was saved"],200);


    }
    


    public function getPost(Request $request)
    {
        //$id = //auth('api')->user()->id;
        
        $id_post = $request->get('id_post');
        $post = (new postModel())->where('id',$id_post)->first();
        $image_set = [];

        $view_count = ($post->view_count);
        $post->view_count = ++$view_count;

        $path = 'public/IN_GOOD_HANDS/'.$post->id_user.'/'.$id_post;
        $images_path = Storage::disk("local")->files($path);
        foreach ($images_path as $key => $file) {
            $image_set[$key] = env('APP_HEROKU_URL').(Storage::url($file));
        }

        $user = (new User())->where('id',$post->id_user)->first();


        return response()->json([
            'id'=> $post->id,
            'title'=> $post->title ,
            'description' => $post->description,
            'date'=> $post->date,
            'id_category'=> $post->id_category,
            'id_user'=> $post->id_user,
            'image_set'=>$image_set,
            'id_city'=>$post->id_city,
            'user_name'=>$user->name,
            'user_created_at'=>date('d-m-Y', strtotime($user->created_at)) 
            ]); 
    }

   
////////////
    public function allPostsData(Request $request)
    {
        $posts = (new postModel())->orderBy('id');

        //return (new postModel())->all()->type();

        return $this->GetPosts($posts);
    
    }

    public function userPostsData(Request $request)
    {
        $id = auth('api')->user()->id;
        $posts = (new postModel())->where('id_user',$id);

        return $this->GetPosts($posts);
    
    }
////////////




    public function GetPosts(mixed $posts)
    {
        $items_num = 10;

        $data = [];
        for($i = 0;$i< $posts->paginate(10)->count();$i++ )
        {
         $data[$i] = ([
            "id"=>$posts->paginate($items_num)->items()[$i]->id,
            "title"=>$posts->paginate($items_num)->items()[$i]->title,
            "description"=>$posts->paginate($items_num)->items()[$i]->description,
            "date"=>$posts->paginate($items_num)->items()[$i]->date,
            "is_active"=>$posts->paginate($items_num)->items()[$i]->is_active,
            "img_set_path"=>env('APP_HEROKU_URL').'/storage'.'/'.$posts->paginate($items_num)->items()[$i]->img_set_path.'/0.jpeg',
            "view_count"=>$posts->paginate($items_num)->items()[$i]->view_count,
            "id_user"=>$posts->paginate($items_num)->items()[$i]->id_user,
            "id_city"=>$posts->paginate($items_num)->items()[$i]->id_city,
            "id_category"=>$posts->paginate($items_num)->items()[$i]->id_category,
            //"id_category"=>$posts->paginate($items_num)->items()[$i]->id_category
       ]);
        
    }

        $anwer = json_encode([
        "page"=>$posts->paginate($items_num)->currentPage(),
        "per_page"=>$posts->paginate($items_num)->count(),//perPage(),,
        "total"=>$posts->paginate($items_num)->total(),
        "total_pages"=>$posts->paginate($items_num)->lastPage(),
        "data"=>$data,
 
    ]);
        return $anwer;
    }


    public function getPhoneNumber(Request $request)
    {
        $id_post = $request->get('id_post');
        $id_user = (new postModel())->where('id',$id_post)->first()->id_user;
        $phone_number = (new User())->where('id',$id_user)->first()->phone_number;
        return $phone_number;
    }



    public function loadPreviewToHeroku()
    {
       
        $posts = (new postModel())::all();

        $paths = [];
        foreach($posts as  $key =>  $post)
        {
            $paths[$key] = 'IN_GOOD_HANDS/'.$post->id_user.'/'.$post->id;
        }
        
        foreach($paths as $key=>$path)
        {
            
            Storage::disk("local")->makeDirectory('public/'.$path);
            for($i = 0;$i < count(Storage::disk("google")->allFiles($path));$i++)
            {
                
                $content = Storage::disk("google")->get($path.'/'.$i.'.jpeg');
                Storage::disk("local")->put('public/'.$path.'/'.$i.'.jpeg',$content);
                
                
            }
        };
        $content = Storage::disk("google")->get('IN_GOOD_HANDS/is_exist.txt');
        Storage::disk("local")->put('public/IN_GOOD_HANDS/is_exist.txt',$content);
        return [Storage::disk("local")->allDirectories(),Storage::disk("local")->allFiles(),201];;
    }



    public function loadPreviewToHerokuTest()
    {
        Storage::disk("local")->deleteDirectory('/public/IN_GOOD_HANDS');
        return [Storage::disk("local")->allDirectories(),Storage::disk("local")->allFiles(),201];;
    }


}