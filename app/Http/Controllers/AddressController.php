<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddressModel;
use Illuminate\Support\Facades\Storage;
use Nette\Utils\Json;

class AddressController extends Controller
{
    public function foo(Request $request)
    {
        $model = new AddressModel();
        
        //Storage::disk('public')->put('movies.json', response()->json($request->input("address")));
        //$model->save();
        return response()->json($model::all(),200);
    }
}
