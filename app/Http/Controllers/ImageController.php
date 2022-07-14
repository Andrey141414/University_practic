<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function load_image(Request $request)
    {
        $image = $request->input('image');
        $data = str_replace(" ", "+", $image);
        $data = base64_decode($data);
        file_put_contents("picture.jpeg",$data);

    }
}
