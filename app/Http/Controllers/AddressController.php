<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddressModel;
use Illuminate\Support\Facades\Storage;

class AddressController extends Controller
{
    public function foo(Request $request)
    {
        $model = new AddressModel();
        $model->title = Storage::disk('public')->put('movies.json', response()->json($request->input("address")));
        $model->save();
        return response()->json(["message"=>"okey"],200);
    }
}
