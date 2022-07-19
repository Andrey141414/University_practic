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
    
    public function addCategoryToDb(Request $request)
    {
        $icon = $request->input('icon');
        $db = new CategoryModel();
        $db->icon = $request->input('icon');
        $db->category_name = $request->input('category_name');
        $db->is_active = true;
        $db->sorting = 0;
        
        $db->save();

        return $this->getCategory();
        //return response()->json(CategoryModel::whereRaw('icon = null')->first());
         
    }

}
