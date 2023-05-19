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
use App\Models\postStatus;
use App\Models\reviewModel;
use App\Service\HelpService;
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
            'created_at' => HelpService::formatDate($user->created_at),
            'rating' => self::calculateRating($id_user),
        ];
    }


    public function getUsersWithPagination($users, $pagination)
    {
        $items_num = $pagination;

        $data = [];

        foreach($users->paginate($pagination) as $user)
        {
            $data[] = $this->userInfo($user);
        }
        $anwer = json_encode([
            "page" => $users->paginate($items_num)->currentPage(),
            "per_page" => $users->paginate($items_num)->count(), //perPage(),,
            "total" => $users->paginate($items_num)->total(),
            "total_pages" => $users->paginate($items_num)->lastPage(),
            "data" => $data,

        ]);


        return $anwer;
    }


    public function userInfo($user)
    {
        
        $reviews = reviewModel::where('id_user_owner', $user->id)->get();
        return [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'email_verified_at' => isset($user->email_verified_at) ? HelpService::formatDate($user->email_verified_at) : null,
            'phone_number' => $user->phone_number,
            'blocked_admin' => $user->blocked_admin,
            'num_login_attempts' => $user->num_login_attempts,
            'city' => CityModel::getCityModel($user->id_city),
            'addresses' => (AddressModel::where('id_user', $user->id))->get(),
            'created_at' => HelpService::formatDate($user->created_at),
            'rating' => UserService::calculateRating($user->id),
            'reviews' => count($reviews),
            'balance' => $user->loyalty_balanse,
            'permissions' => RolePolicyService::getUsreRoles($user->id),
        ];
    }

    public static function queryFilter($query,$prop)
    {
        if (isset($prop['email'])) {
            $query->where('email', 'like', "%{$prop['email']}%");
        }
        if (isset($prop['phone'])) {
            $query->where('phone', 'like', "%{$prop['phone']}%");
        }        
        if (isset($prop['id_user'])) {
            $query->where('id', $prop['id_user']);
        }

        if (isset($prop['email'])) {
            $query->where('email', $prop['email']);
        }

        return $query;
    }
}
