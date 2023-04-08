<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CityModel;
use App\Models\CategoryModel;
use Illuminate\Support\Facades\Validator;

class tapeController extends Controller
{
/** 
    * @OA\Get(
    *     path="/api/city/all_cities",
    *     summary="Получение списка городов",
    *     tags={"Dictionaries"},
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    * )
*/
    public function getAllCitys()
    {
        return response()->json(CityModel::all(),200);
    }

/** 
    * @OA\Get(
    *     path="/api/category/all_categories",
    *     summary="Получение списка категорий",
    *     tags={"Dictionaries"},
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    * )
*/
    public function getAllCategory()
    {
        return response()->json(CategoryModel::all(),200);
    }
    
    public function addCategoryToDb(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'icon' => 'required',
            'name' => 'required|unique:category|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }

        $icon = $request->input('icon');
        $db = new CategoryModel();
        $db->icon = $request->input('icon');
        $db->name = $request->input('name');
        $db->is_active = true;
        $db->sorting = 0;
        
        $db->save();

        return $this->getCategory();
        //return response()->json(CategoryModel::whereRaw('icon = null')->first());
         
    }

}
