<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AddressModel;
class AddressController extends Controller
{
    public function foo(Request $request)
    {
        $model = new AddressModel();
        $model->title = $request->input("address");
        $model->save();
        return response()->json(["message"=>"okey"],200);
    }
}
