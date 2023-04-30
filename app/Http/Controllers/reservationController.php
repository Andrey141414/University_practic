<?php

namespace App\Http\Controllers;

use App\Models\reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use App\Models\BidModel;
use App\Models\reservationStatus;
use App\Service\ReservationService;
use App\Service\BidService;
use App\Service\PostService;
use Google\Service\Compute\Resource\Reservations;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class reservationController extends Controller
{
    protected $pagination = 10;
    



    /** 
     * @OA\Delete(
     *     path="/api/delete_reservation",
     *     summary="Удаление брони",
     *     security={
     *           {"passport": {}},
     *      },         *     
     *     tags={"Reservation"},
     *     @OA\Parameter(
     *       name="id_reservation",
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
    public function deleteReservation(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_reservation' => 'required|integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        return ReservationService::Delete($request->get('id_reservation'));
    }


/** 
     * @OA\Get(
     *     path="/api/get_reservations",
     *     summary="Получить бронирования",
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
     *       name="statuses[]",
     *       in="query",
     *       required=true,
     *      @OA\Schema(type="array", @OA\Items(type="string")),
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
    public function getReservations(Request $request)
    {
        $this->validator->set($request->all(), [
            'statuses' => 'required|between:1,5',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        $reservations = ReservationService::getAllOutputRes($request->get('statuses'));
        $postOnPage = $request->get('limit');
        if (!$postOnPage) {
            $postOnPage = $this->pagination;
        }
        return ReservationService::getResWithPagination($reservations, $postOnPage);
    }

/** 
     * @OA\Patch(
     *     path="/api/change_reservation_status",
     *     summary="Изменить статус брони",
     *     security={
     *           {"passport": {}},
     *      },     
     *     tags={"Reservation"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_reservation","status"},
     *       @OA\Property(property="id_reservation", type="int",example=10),
     *       @OA\Property(property="status", type="string",example="cancel",)
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
    public function changeStatus(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_reservation' => 'required|integer',
            'status' => 'required|string',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        return ReservationService::changeStatus($request->get('id_reservation'), $request->get('status'));
    }


    public function requestReservations(){

    }
}
