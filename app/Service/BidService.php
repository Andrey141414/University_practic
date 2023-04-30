<?php


namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\postFilterRequest;
use App\Models\AddressModel;
use App\Models\CategoryModel;
use App\Models\CityModel;
use App\Models\favoritePost;
use App\Models\reservation;
use App\Models\reservationStatus;
use App\Models\reviewModel;
use App\Models\BidModel;
use App\Service\UserService;

class BidService
{


    public static function Create($id_post, $days)
    {
        if ($days > 3 || $days < 1) {
            return 'Error - Неверно указали колличество дней';
        }
        if (auth('api')->user()->id == PostModel::find($id_post)->id_user) {
            return 'Error - Нельзя забронировать своё объявление';
        }
        if (BidModel::where('id_post', $id_post)->where('id_user', auth('api')->user()->id)->first()) {
            return 'Error - Бронь уже существует';
        }
        $props['id_post'] = $id_post;
        $props['id_user'] = auth('api')->user()->id;
        $props['days'] = $days;
        $props['created_at'] = now();
        $bid = BidModel::Create($props);

        return $bid;
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


    public static function getBidsWithPagination($bids, $postOnPage)
    {
        $data = [];
        $i = 0;
        foreach ($bids as $bid) {
            $data[$i]['id'] = $bid->id;
            $data[$i]['post'] = PostService::getPostResponse($bid->id_post)['post'];
            $data[$i]['user'] = UserService::getShortUserModel($bid->id_user);
            $data[$i]['days'] = $bid->days;
            $data[$i]['created_at'] = $bid->created_at;
            $i++;
        }


        return json_encode([
            "page" => $bids->paginate($postOnPage)->currentPage(),
            "per_page" => $bids->paginate($postOnPage)->count(),
            "total" => $bids->paginate($postOnPage)->total(),
            "total_pages" => $bids->paginate($postOnPage)->lastPage(),
            "data" => $data,

        ]);
    }

    public static function getIncoming($id_user)
    {
        $posts = PostModel::select('id')->where('id_user', $id_user)->get();
        $bids = BidModel::whereIn('id_post', $posts)->orderBy('created_at','desc')->get();
        return $bids;
    }

    public static function confirmBid($id_bid)
    {
        $bid = BidModel::find($id_bid);
        if ($bid) {
            $res = ReservationService::Create($bid);
            if ($res) {
                $bid->delete();    
            }
            return $res;
        }

        return null;
    }
}
