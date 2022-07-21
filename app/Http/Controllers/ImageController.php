<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class ImageController extends Controller
{
    public function load_image(Request $request)
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
        
        
        $image = $request->input('image_set');
        $data = str_replace(" ", "+", $image);
        
        
        Storage::disk("google")->makeDirectory('IN_GOOD_HANDS/'.$id);
        Storage::disk("google")->makeDirectory('IN_GOOD_HANDS/'.$id.'/'.$id_post);
        $path = 'IN_GOOD_HANDS/'.$id.'/'.$id_post.'/picture.jpeg';


        $data = base64_decode($data);
        Storage::disk("google")->put($path,$data);


//записать путь в базу
//добавить в конфиги гугл


        //Storage::disk("google")->makeDirectory('DIRECTORY1');
        //$image = Storage::disk("google")->get('DIRECTORY1\hellowWord.txt');
    
        //$path = Storage::path('file.jpg');
        return response()->json([
            'message' => 'Sucsessfuly'
        ], 200);
    }
}
