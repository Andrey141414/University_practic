<?php


namespace App\Service;

use App\Http\Controllers\reviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\postFilterRequest;
use App\Models\AddressModel;
use App\Models\BidModel;
use App\Models\CategoryModel;
use App\Models\CityModel;
use App\Models\favoritePost;
use App\Models\reservation;
use App\Models\reservationStatus;
use App\Models\reviewModel;
use App\Service\UserService;
use App\Service\LoyalitySystem;
use App\Jobs\ReservationCompletedJob;

class ReservationService
{

    public static function getShortReservationModel($id_res)
    {
        $res = reservation::find($id_res);
        if (!$res) {
            return null;
        }

        return [
            'id' => $res->id,
            'user' => UserService::getShortUserModel($res->id_user),
            'status' => $res->status,
            'expired_at' => date('d-m-Y', strtotime($res->expired_at)),
        ];
    }
    public static function Create($bid)
    {
        $days = $bid->days;
        $id_post = $bid->id_post;
        $id_user = $bid->id_user;

        if (reservation::where('id_post', $id_post)->where('id_user', $id_user)->first()) {
            return null;
        }
        $props['id_post'] = $id_post;
        $props['id_user'] = $id_user;
        $props['created_at'] = Carbon::now();
        $props['days'] = $days;
        $props['expired_at'] = Carbon::now()->addDays($days);
        $props['status'] = 'order';


        $res = reservation::Create($props);

        $post = postModel::find($res->id_post);
        $post->Update([
            'status' => 'reserved',
        ]);
        return $res;
    }

    public static function Delete($id_res)
    {
        $res = reservation::find($id_res);
        if (!$res) {
            return response()->json('Такой брони нет', 404);
        }
        $res->delete();
        return response()->json('Бронь удалена', 200);
    }

    public static function getAllInputRes()
    {
        if (auth('api')->user()) {
            $id = auth('api')->user()->id;
        } else {
            $id = 16;
        }

        $myPosts = postModel::select('id')->where('id_user', $id)->get();

        return  reservation::whereIn('id_post', $myPosts)->where('status', 'bid')->get();
    }

    public static function getAllOutputRes($statuses)
    {
        if (auth('api')->user()) {
            $id = auth('api')->user()->id;
        } else {
            $id = 16;
        }
        return reservation::where('id_user', $id)->whereIn('status', $statuses)->orderBy('created_at', 'desc')->get();
    }



    public static function getResWithPagination($reservations, $postOnPage, $type = null)
    {
        $data = [];
        $i = 0;
        foreach ($reservations as $res) {
            $data[$i]['id'] = $res->id;

            $contacts = null;
            if ($res->status == 'order' || $res->status == 'confirm_sent') {
                $post = postModel::find($res->id_post);
                $contacts = [
                    'phone' => User::find($post->id_user)->phone_number,
                    'address' => json_decode(postModel::find($res->id_post)->address),
                ];
                if ($post->show_email) {
                    $contacts['email'] = User::find($post->id_user)->email;
                }
            } else if ($res->status == 'completed' && reviewModel::where('id_reservation', $res->id)->first()) {
                $post = postModel::find($res->id_post);
                $data[$i]['review'] = self::getResReview($res->id);
            }

            $data[$i]['post'] = PostService::getPostResponse($res->id_post)['post'];

            if (isset($contacts)) {
                $data[$i]['contacts'] = $contacts;
            }
            $data[$i]['user'] = UserService::getShortUserModel($res->id_user);
            $data[$i]['days'] = $res->days;
            $data[$i]['status'] = $res->status;
            $expired_at = ($type == 'OUTPUT') ? Carbon::parse($res->created_at)->addDays($res->days)->format('d-m-Y') : null;
            $data[$i]['expired_at'] = $expired_at;
            $data[$i]['created_at'] = $res->created_at;
            $i++;
        }


        $anwer = json_encode([
            "page" => $reservations->paginate($postOnPage)->currentPage(),
            "per_page" => $reservations->paginate($postOnPage)->count(),
            "total" => $reservations->paginate($postOnPage)->total(),
            "total_pages" => $reservations->paginate($postOnPage)->lastPage(),
            "data" => $data,

        ]);


        return $anwer;
    }

    public static function changeStatus($id_res, $status)
    {
        if ($res = (new reservationStatus)->checkStatusMove($id_res, $status)) {

            $updateProps = [
                'status' => $status,
            ];
            if ($status == 'completed') {
                $post = postModel::find($res->id_post);
                $post->Update([
                    'status' => 'closed',
                ]);

                $loyality = new LoyalitySystem(User::find($post->id_user));
                $loyality->reservationClose();
                //удаляем из заявок
                $bids = BidModel::where('id_post', $res->id_post)->get();
                foreach ($bids as $bid) {
                    $bid->delete();
                }
                //Отправляем сообщение на почту
                dispatch(new ReservationCompletedJob(
                    User::find($post->id_user),
                    $res,
                ));
                //Отправляем сообщение на почту
                dispatch(new ReservationCompletedJob(
                    User::find($res->id_user),
                    $res,
                ));
            }

            if ($status == 'cancel') {
                $post = postModel::find($res->id_post);
                $post->Update([
                    'status' => 'closed',
                ]);
            }

            $res->Update($updateProps);
            return $res;
        }
        return response()->json('У текущей брони невозможно поставить такой статус', 415);
    }

    public static function getResReview($id_res)
    {
        $review = reviewModel::where('id_reservation', $id_res)->first();
        if ($review) {
            $review = json_decode($review, true);
            $review['created_at'] = date('d-m-Y', strtotime($review['created_at']));
            $review['user_writer'] = UserService::getShortUserModel($review['id_user_writer']);
            unset($review['id_user_writer']);
        }
        return $review;
    }
}
