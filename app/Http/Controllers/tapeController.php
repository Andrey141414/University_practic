<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CityModel;
use App\Models\CategoryModel;

class tapeController extends Controller
{
    public function setAllCitys()
    {
        $model = new CityModel();
        return response()->json($model::all(),200);
    }
    public function getCategory()
    {
        return response()->json(CategoryModel::all(),200);
    }
    
    public function addIconToDb(Request $request)
    {
        $icon = $request->input('icon');
        $db = new CategoryModel();
        $db->icon = $icon;
        $db->save();


        return response()->json(CategoryModel::whereRaw('icon = null')->first());
         
    }

}
