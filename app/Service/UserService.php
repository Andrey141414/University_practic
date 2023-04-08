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
use App\Models\statusModel;
use App\Models\reviewModel;


class UserService
{
    public static function getAllUserPosts($id_user)
    {
        return postModel::where('id_user', $id_user)->get();
    }


    public static function calculateRating($id_user){
        $rating = 0;

        $reviews = reviewModel::where('id_user_owner', $id_user)->get();
        foreach ($reviews as $review) {
            $rating += $review->score;
        }

        if(count($reviews) == 0)
        {
            $rating = 0;
        }
        else
        {
            $rating /= count($reviews);
        }

        return (double)$rating;
    }

    public static function getShortUserModel($id_user)
    {
        $user = User::find($id_user);
        return [
            'id' =>$id_user,
            'name' => $user->name,
            'created_at' => date('d-m-Y', strtotime($user->created_at)),
            'rating' => self::calculateRating($id_user),
        ];
    }
}
