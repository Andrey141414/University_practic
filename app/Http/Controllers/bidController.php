<?php

namespace App\Http\Controllers;


use App\Models\reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use App\Models\BidModel;
use App\Service\ReservationService;
use App\Service\BidService;
use App\Service\PostService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class bidController extends Controller
{
    //
    protected $pagination = 10;

    /** 
     * @OA\Post(
     *     path="/api/send_bid",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Отправить заявку на бронирование",
     *     tags={"Reservation"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_post","days"},
     *       @OA\Property(property="id_post", type="int",example=111),
     *       @OA\Property(property="days", type="int",example=2,)
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
    public function sendBid(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_post' => 'required|integer',
            'days' => 'required|integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $post = postModel::find($request->input('id_post'));
        if ($post && $post->status == 'active') {
               $bid = BidService::Create(
                $request->input('id_post'),
                $request->input('days')
            );
            return response()->json($bid);
        }

        return response()->json('Error',405);
    }

    /** 
     * @OA\Get(
     *     path="/api/get_bids",
     *     summary="Получить заявки на бронирование (входящие или исходящие)",
     *     security={
     *           {"passport": {}},
     *      },    
     *     tags={"Reservation"},
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=false,
     *       example = 3,
     *       ), 
     *       @OA\Parameter(
     *       name="page",
     *       in="query",
     *       required=false,
     *       example = 1,
     *       ),
     *      @OA\Parameter(
     *       name="filter",
     *       in="query",
     *       required=true,
     *       example = "outcoming",
     *       ), 
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *     ),
     * ),
     */
    public function getBids(Request $request)
    {
        $this->validator->set($request->all(), [
            'filter' => 'required|string',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        if (strcmp($request->get('filter'), 'incoming') == 0) {
            $bids = BidService::getIncoming(auth('api')->user()->id);
        } else if (strcmp($request->get('filter'), 'outcoming') == 0) {
            $bids = BidModel::where('id_user', auth('api')->user()->id)->orderBy('created_at', 'desc')->get();
        } else {
            return 123;
        }
        $postOnPage = $this->pagination;
        if ($request->get('limit')) {
            $postOnPage = $request->get('limit');
        }

        //$bids->orderBy('created_at','desc');
        return BidService::getBidsWithPagination($bids, $postOnPage);
    }


    /** 
     * @OA\Post(
     *     path="/api/confirm_bid",
     *     summary="Подтвердить заявку на бронирование",
     *     security={
     *           {"passport": {}},
     *      },     
     *     tags={"Reservation"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_bid"},
     *       @OA\Property(property="id_bid", type="int",example=10),
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
     *         response="401",
     *         description="Unauthorized",
     *     ),
     * ),
     */
    public function confirmBid(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_bid' => 'required|integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $res = BidService::confirmBid($request->input('id_bid'));
        return json_encode($res);
    }

    /** 
     * @OA\Delete(
     *     path="/api/delete_bid",
     *     summary="Отклонить\удалить заявку на бронирование",
     *     security={
     *           {"passport": {}},
     *      },     
     *     tags={"Reservation"},
     *     @OA\Parameter(
     *       name="id_bid",
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
     *         response="401",
     *         description="Unauthorized",
     *     ),
     * ),
     */
    public function deleteBid(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_bid' => 'required|integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $bid = BidModel::find($request->input('id_bid'));
        if ($bid) {
            $bid->delete();
            return 'Заявка успешно удалена';
        }
        return 'Error такой заявки не существует';
    }
}
