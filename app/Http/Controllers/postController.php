<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\postModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

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
        
        // = preg_split("/[\s,]+/",$image);
        
        
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
        $path = $post->img_set_path;
        $post->delete();
        Storage::disk("google")->deleteDirectory($path);
        return response()->json(["message"=>"Data was deleted"],200);

    }
    public function changePost(Request $request)
    {
        $id = auth('api')->user()->id;
        $id_post = $request->input('id_post');
        $post = (new postModel())->where('id',$id_post)->first();
        //return response()->json($id_post,200);
        $post->title = $request->input('title');
        $post->description = $request->input('description');
        $post->id_category = $request->input('id_category');

        
        Storage::disk("google")->deleteDirectory($post->img_set_path);

        $image = $request->input('image_set');
        $images = preg_split("/[\s,]+/",$image);
        Storage::disk("google")->makeDirectory('IN_GOOD_HANDS/'.$id);
        Storage::disk("google")->makeDirectory('IN_GOOD_HANDS/'.$id.'/'.$id_post);

        //цикл
        foreach ($images as $key => $data) {
            $path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post.'/'.$key.'.jpeg';
            $data = base64_decode($data);
            Storage::disk("google")->put($path,$data);
        }
        //конец цикла
        
        $post->img_set_path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post;
        $post->save();
        return response()->json(["message"=>"Data was saved"],200);


    }
    

    public function getPost(Request $request)
    {
        $id = 18;//auth('api')->user()->id;
        $id_post = $request->get('id_post');
        $post = (new postModel())->where('id',$id_post)->first();
        $image_set = "";


        $path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post;
        $images_path = Storage::disk("google")->files($path);
        foreach ($images_path as $key => $file) {
            $image_set = $image_set.' '.base64_encode(Storage::disk("google")->get($file));
        }

        return [$this->postInfo($post),$image_set];
    }

    public function postInfo(postModel $post)
    {
        return response()->json([
            'id'=> $post->id,
            'title'=> $post->title ,
            'description' => $post->description,
            'date'=> $post->date,
            'id_category'=> $post->id_category,
            'id_user'=> $post->id_user,
            ]);
    }


    public function myPosts(Request $request)
    {
        $id = 18;// = auth('api')->user()->id;
        $user_posts = (new postModel())->where('id_user',$id)->get();



        $items_num = 2;
        $previews = array();

        for($i = 0;$i<$items_num;$i++)
        {
            $path = (new postModel())->where('id_user',$id)->simplePaginate($items_num)->items()[$i]->img_set_path;
            array_push($previews,base64_encode(Storage::disk("google")->get($path.'/0.jpeg')));
        }
   
        return ((new postModel())->where('id_user',$id)->simplePaginate($items_num));
    }

    public function allPostsData(Request $request)
    {
        $posts = (new postModel())->orderBy('id');
        


        

        $items_num = 4;
        $data = array();
        for($i = 0;$i< $items_num;$i++ )
        {
         $data[$i] = json_encode([
            "id"=>$posts->paginate(4)->items()[$i]->id,
            "title"=>$posts->paginate(4)->items()[$i]->title,
            "description"=>$posts->paginate(4)->items()[$i]->description,
            "date"=>$posts->paginate(4)->items()[$i]->date,
            "is_active"=>$posts->paginate(4)->items()[$i]->is_active,
            "img_set_path"=>env('APP_HEROKU_URL').'/storrage'.'/'.$posts->paginate(4)->items()[$i]->img_set_path.'/jpeg.0',
            "view_count"=>$posts->paginate(4)->items()[$i]->view_count,
            "id_user"=>$posts->paginate(4)->items()[$i]->id_user,
            "id_city"=>$posts->paginate(4)->items()[$i]->id_city
       ]);
        
    }

        $anwer = json_encode([
        "page"=>$posts->paginate(4)->currentPage(),
        "per_page"=>$posts->paginate(4)->count(),//perPage(),,
        "total"=>$posts->paginate(4)->total(),
        "total_pages"=>$posts->paginate(4)->lastPage(),
        "data"=>$data,
 
    ]);
        return $anwer;
        //->items()[0]->id;
    
    }


    public function allPostsPhoto(Request $request)
    {
        $previews = array();
        $previews  = (new postModel())->pluck('img_set_path')->toArray();
        foreach ($previews as $key => $file)
        {
            $previews[$key] = base64_encode(Storage::disk("local")->get($file.'/0.jpeg'));
        }
         
        return $previews;
    
    }





    public function loadPreviewToHeroku()
    {
        $posts = (new postModel())::all();
        foreach($posts as $post)
        {

            $path = 'IN_GOOD_HANDS/'.$post->id_user.'/'.$post->id;
            $content = Storage::disk("google")->get($path.'/0.jpeg');
            Storage::disk("local")->makeDirectory('public/'.$path);
            Storage::disk("local")->put('public/'.$path.'/0.jpeg',$content);

        } 

        return [Storage::disk("local")->allDirectories(),Storage::disk("local")->allFiles()];;

    }
   
}
