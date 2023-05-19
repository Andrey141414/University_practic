<?php

namespace App\Http\Controllers;

use App\Models\postModel;
use App\Models\reservation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\reviewModel;
use App\Models\User;
use App\Service\UserService;
use Carbon\Carbon;
use App\Service\LoyalitySystem;
use App\Service\HelpService;

class reviewController extends Controller
{

    protected $pagination = 5;
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
     *       @OA\Property(property="id_reservation", type="int",example=1),
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


        $this->validator->set($request->all(), [
            'score' => 'required',
            'text' => 'required',
            'id_reservation' => 'required',
        ]);

        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }


        $res = reservation::find($request->input('id_reservation'));
        if (!$res) {
            return response()->json('Нет такой брони', 404);
        }
        if ($res->status == 'completed' && $id_user_writer == $res->id_user) {
            $post = postModel::find($res->id_post);
            $review = reviewModel::create([
                'text' => $request->input('text'),
                'score' => (int)$request->input('score'),
                'id_user_writer' => $id_user_writer,
                'id_user_owner' => $post->id_user,
                'id_reservation' => $request->input('id_reservation'),
                'created_at' => Carbon::now(),
            ]);

            $loyality = new LoyalitySystem(User::find($post->id_user));
            $loyality->setScoreOnPost($request->input('score'));

            $loyality = new LoyalitySystem(User::find($id_user_writer));
            $loyality->setReviewOnPost();
            return response()->json($this->getRewiewResponse($review), 200);
        }
        return response()->json('Error', 405);
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
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=false,
     *       example = 3,
     *       ),
     *     @OA\Parameter(
     *       name="score",
     *       in="query",
     *       required=false,
     *       example = "4",
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
        $this->validator->set($request->all(), [
            'id_user_owner' => 'required',
            'limit' => 'integer',
            'score' => 'integer'
        ]);

        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $props = $request->all();
        $orderScore = (isset($props['score'])) ? $props['score'] : null;
        $reviews = collect($this->getUserReviews((int)$request->id_user_owner,$orderScore));
        $this->pagination = isset($props['limit']) ? $props['limit'] : $this->pagination;
        
        return response()->json($this->getReviewsWithPagination($reviews, $this->pagination), 200);
    }

    /** 
     * @OA\Get(
     *     path="/api/get_my_reviews",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Посмотреть отзывы на себя",
     *     tags={"Review"},
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=false,
     *       example = 3,
     *       ),
     *     @OA\Parameter(
     *       name="score",
     *       in="query",
     *       required=false,
     *       example = "3",
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
    public function getMyReviews(Request $request)
    {
        $this->validator->set($request->all(), [
            'limit' => 'integer',
            'score' => 'string'
        ]);

        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        $props = $request->all();
        $this->pagination = isset($props['limit']) ? $props['limit'] : $this->pagination;
        $orderScore = (isset($props['score'])) ? $props['score'] : null;
        $id_user_owner = auth('api')->user()->id;
        $reviews = collect($this->getUserReviews($id_user_owner, $orderScore));
        return response()->json($this->getReviewsWithPagination($reviews, $this->pagination), 200);
    }

    function getUserReviews($id_user_owner, $orderScore = null)
    {
        $query = reviewModel::query();
        if ($orderScore) {
            $query->where('score', $orderScore);
        }
        $reviews = $query->orderBy('created_at', 'desc')->where('id_user_owner', $id_user_owner)->get();
        foreach ($reviews as $review) {
            json_decode($review, true);
            $review['user_writer'] =  UserService::getShortUserModel($review->id_user_writer);
            $review['created_at'] =  HelpService::formatDate($review['created_at']);
            unset($review['id_user_writer']);
        }


        return $reviews;
    }

    function getRewiewResponse($review)
    {
        json_decode($review, true);
        $review['user_writer'] =  UserService::getShortUserModel($review->id_user_writer);
        $review['created_at'] =  HelpService::formatDate($review['created_at']);
        unset($review['id_user_writer']);
        return $review;
    }


    public function getReviewsWithPagination($reviews, $pagination)
    {
        $items_num = $pagination;
        $data = [];
        foreach ($reviews->paginate($items_num)->items() as $review) {

            $data[] = json_decode($review, true);
        }
        $anwer = [
            "page" => $reviews->paginate($items_num)->currentPage(),
            "per_page" => $reviews->paginate($items_num)->count(), //perPage(),,
            "total" => $reviews->paginate($items_num)->total(),
            "total_pages" => $reviews->paginate($items_num)->lastPage(),
            "data" => $data,

        ];


        return $anwer;
    }
}
