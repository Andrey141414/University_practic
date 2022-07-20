<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\postModel;


class postController extends Controller
{
    public function createPost(Request $request)
    {
        $id = auth('api')->user()->id;
        

        DB::table('post')->insert([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'date' => $request->input('title'),
            'view_count' => $request->input('title'),
            'id_category' => $request->input('title'),
            'id_user' => $id,
        ]);

        $id_post =  DB::table('post')->max('id');
        
        
        $image = $request->input('image');
        
        //на стороне клиента
        //$data = str_replace(" ", "+", $image);
        $images = preg_split("/[\s,]+/",$image);
        
        
        
        Storage::disk("google")->makeDirectory('IN_GOOD_HANDS/'.$id);
        Storage::disk("google")->makeDirectory('IN_GOOD_HANDS/'.$id.'/'.$id_post);
        

        
        //цикл
        foreach ($images as $key => $data) {
            $path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post.'/'.$key.'.jpeg';
            $data = base64_decode($data);
            Storage::disk("google")->put($path,$data);
        }


        // $path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post.'/picture.jpeg';
        // $data = base64_decode($data);
        // Storage::disk("google")->put($path,$data);
        // //конец цикла

        $post = (new postModel())->where('id',$id_post)->first();
        $post->img_set_path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post;
        $post->save();

        //Storage::disk("google")->makeDirectory('DIRECTORY1');
        //$image = Storage::disk("google")->get('DIRECTORY1\hellowWord.txt');
    
        //$path = Storage::path('file.jpg');
        return 200;
    }
}
