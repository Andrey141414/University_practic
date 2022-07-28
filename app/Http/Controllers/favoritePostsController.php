<?php

namespace App\Http\Controllers;

use App\Models\favoritePost;
use Illuminate\Http\Request;
use App\Models\postModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
}
