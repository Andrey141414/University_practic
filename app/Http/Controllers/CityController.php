<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CityModel;
class CityController extends Controller
{
   
    public function setAllCitys(Request $request)
    {
        $model = new CityModel();
        //Storage::disk('public')->put('movies.json', response()->json($request->input("address")));
        //$model->id_user = 1;

        return response()->json($model::all(),200);
    }

    public function GetCity(Request $request)
    { 
        $model = new CityModel();
        //$model->name = json_encode( $request->input());
        $model->name = $request->input('name');
        $model->save();
    }
}
