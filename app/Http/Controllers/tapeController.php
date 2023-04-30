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
        return response()->json(CityModel::where('is_active',true)->get(),200);
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
        return response()->json(CategoryModel::where('is_active',true)->get(),200);
    }
}
