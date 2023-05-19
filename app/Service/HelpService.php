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

class HelpService
{

    public static function formatDate($date)
    {
        return date('d-m-Y', strtotime($date));
    }
}
