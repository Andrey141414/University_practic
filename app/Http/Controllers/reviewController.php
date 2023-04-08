<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\reviewModel;
use App\Service\UserService;
use Carbon\Carbon;

class reviewController extends Controller
{
/** 
    * @OA\Post(
    *     path="/api/create_review",
    *     security={
    *           {"passport": {}},
    *      },    
    *     summary="Создание Отзыва",
    *     tags={"Review"},
    *     @OA\RequestBody(
    *     required=true,
    *     description="Pass user credentials",
    *     @OA\JsonContent(
    *       required={"id_user_owner","score","text"},
    *       @OA\Property(property="id_user_owner", type="int",example=1),
    *       @OA\Property(property="score", type="int",example=5),
    *       @OA\Property(property="text", type="string",example="Новый отзыв"),
    *    ),
    * ),
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="Validation error",
    *     ),
    * ),
    */
    public function createReviews(Request $request)
    {
        $id_user_writer = auth('api')->user()->id;
        $validator = Validator::make($request->all(), [
            'id_user_owner' => 'required',
            'score' => 'required',
            'text' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }

        $review = reviewModel::create([
            'text' => $request->input('text'),
            'score' => $request->input('score'),
            'id_user_writer' => $id_user_writer,
            'id_user_owner' => (int)$request->input('id_user_owner'),
            'created_at' => Carbon::now(),
        ]);

        return response()->json($review, 200);
    }

/** 
    * @OA\Get(
    *     path="/api/get_user_reviews",
    *     security={
    *           {"passport": {}},
    *      },    
    *     summary="Посмотреть отзывы пользователя",
    *     tags={"Review"},
    *     @OA\Parameter(
    *       name="id_user_owner",
    *       in="query",
    *       required=true,
    *       example = 9,
    *       ), 
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="Validation error",
    *     ),
    * ),
*/
    public function getReviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user_owner' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }
        return $this->getUserReviews((int)$request->id_user_owner);
        
        // $reviews = reviewModel::where('id_user_owner',(int)$request->id_user_owner)->get();
        // return response()->json($reviews,200);
      
    }

/** 
    * @OA\Get(
    *     path="/api/get_my_reviews",
    *     security={
    *           {"passport": {}},
    *      },    
    *     summary="Посмотреть отзывы на себя",
    *     tags={"Review"}, 
    *     @OA\Response(
    *         response=200,
    *         description="successful operation",
    *         @OA\Schema(
    *             type="string",         
    *         ),
    *     ),
    *     @OA\Response(
    *         response="400",
    *         description="Validation error",
    *     ),
    * ),
*/
    public function getMyReviews()
    {
        $id_user_owner = auth('api')->user()->id;
        return $this->getUserReviews($id_user_owner);
    }

    function getUserReviews($id_user_owner)
    {
        $reviews = reviewModel::orderBy('created_at', 'desc')->where('id_user_owner',$id_user_owner)->get();
        foreach($reviews as $review)
        {
            json_decode($review,true);
            $review['user_writer'] =  UserService::getShortUserModel($review->id_user_writer);
            $review['created_at'] =  date('d-m-Y', strtotime($review['created_at']));
            unset($review['id_user_writer']);
        }
        return response()->json($reviews,200);
    }
}
