<?php

namespace App\Http\Controllers;

use App\Models\favoritePost;
use Illuminate\Http\Request;
use App\Models\postModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\postFilterRequest;
class favoritePostsController extends Controller

{
    //
    public function addPostToFavorive(Request $request)
    {
        $id_user = auth('api')->user()->id;
        $id_post = $request->get('id_post');

        
        $favoritePost = (new favoritePost());
        $posts = (new postModel());

        
        if($posts->where('id',$id_post)->first()==null)
        {
            return response()->json([
                "message" => "post is missing"
            ], 404);
        }
     
        

        $check_post= $favoritePost::where(function ($query) use ($id_post,$id_user) {
            $query->where('id_post', $id_post)
                  ->where('id_user', $id_user);
        })->first();

        
        if($check_post!=null)
        {
            return response()->json([
            "message" => "post already added"
        ], 200);
        }

        DB::table('favorite_post')->insert([

            'id_post'=>$id_post,
            'id_user'=>$id_user
        ]);
        
        return response()->json(["mewssage"=>"post has been added to favorites"],200); 

    }






    public function deletePostFromFavorite(Request $request)
    {
        $id_user = auth('api')->user()->id;
        $id_post = $request->get('id_post');

        $favoritePost = (new favoritePost())->where('id_post',$id_post)->first();
        //$posts = (new postModel());

        
        if($favoritePost==null)
        {
            return response()->json([
                "message" => "post is missing in favorite"
            ], 404);
        }
        
        $favoritePost->delete();
        return response()->json(["mewssage"=>"post has been deleted from favorites"],200); 

    }





    public function allFavoritePostsID(postFilterRequest $request)
    {
       
        $id_user = auth('api')->user()->id;

        $id_posts = (new favoritePost())->where('id_user',$id_user)->pluck('id_post');
        
        $data=$request->validated();
        $query = postModel::query();
        if(isset($data['title']))
        {
            $query->where('title','ilike',"%{$data['title']}%");
        }
        
        $posts = $query->get();
        
        $id_posts = $posts->find($id_posts)->pluck('id');


        return $id_posts;
    }

    public function allFavoritePosts(postFilterRequest $request)
    {
        $id_user = auth('api')->user()->id;

        $id_posts = (new favoritePost())->orderBy('id')->where('id_user',$id_user)->pluck('id_post');
        $id_posts1 = (new favoritePost())->orderBy('id')->where('id_user',$id_user)->pluck('id_post');
        // $massiv = [];
        // $massiv = $id_posts;
        // return $massiv;

        $i=0;$j=$id_posts->count()-1;
         
        foreach($id_posts as $data)
        {
            $id_posts1[$j] = $id_posts[$i];
            $i++;
            $j--;
        }



        $data = $request->validated();
        $query = postModel::query();
        if(isset($data['title']))
        {
            $query->where('title','ilike',"%{$data['title']}%");
        }
        
        $posts = $query->get();
        $id_post_short = $posts->pluck('id');
        //return [$id_post_short,$id_posts1];

        $id_post_result = [];;
        for($i = 0,$k = 0;$i<$id_posts1->count();$i++)
        {
            for($j = 0;$j<$id_post_short->count();$j++)
            {
                if($id_post_short[$j]==$id_posts1[$i])
                {
                    $id_post_result[$k] = $id_posts1[$i];
                    $k++;
                    break;
                }
            }    
        }
        //return [$id_post_result,$id_posts1];

        $posts = $posts->find($id_post_result);
        $buff = $posts->find($id_post_result);
        //return $buff;
        
        $i = 0;
        foreach($posts as $key => $post)
        {
           $a = $buff->where('id',$id_post_result[$i])->first();
           $posts[$key] = $a;
           $i++;
        }
        //return $posts;
        return (new postController())->GetPosts($posts);
    }



   
}
