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

class LoyalitySystem
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function addGoals($goals)
    {
        $this->user->loyalty_balanse += $goals;
        $this->user->save();
    }

    public function takeAwayGoals($goals)
    {
        $this->user->loyalty_balanse -= $goals;
        $this->user->save();
    }

    public function setScoreOnPost($score)
    {
        if ($score >= 3) {
            $this->addGoals($score - 2);
        }
    }


    public function reservationClose()
    {
        $this->addGoals(3);
    }

    public function setReviewOnPost()
    {
        $this->addGoals(1);
    }
}
