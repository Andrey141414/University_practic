<?php

namespace App\Http\Controllers;

use App\Http\Requests\testRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CityModel;
use Illuminate\Support\Facades\Storage;
use App\Models\AddressModel;
use App\Models\postModel;
use App\Models\reviewModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Validator;
use App\Service\UserService;
use Illuminate\Support\Carbon;
use App\Models\postStatus;
use App\Service\PostService;
use App\Service\RolePolicyService;
use Intervention\Image\Facades\Image;
use App\Service\HelpService;
class userController extends Controller
{

/** 
     * @OA\Get(
     *     path="/api/get_short_user_info",
     *     security={
     *           {"passport": {}},
     *      },
     *     summary="Получить краткую информацию о пользователе",     
     *     tags={"User"},
     *     @OA\Parameter(
     *       name="id_user",
     *       in="query",
     *       required=true,
     *       example = 8,
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
    public function getShortUserInfo(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_user' => 'required|integer|exists:users,id',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        $id_user = $request->get('id_user');

        return response()->json(UserService::getShortUserModel($id_user),200);
    }

    /**
     * @OA\Patch(
     *     path="/api/change_user_info",
     *     summary="Изменение данных пользователя",
     *     tags={"User"},
     *     security={
     *           {"passport": {}},
     *      },
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *      @OA\JsonContent(
     *       @OA\Property(property="name", type="string", example="Новое имя"),
     *       @OA\Property(property="email", type="email", example="new@.com"),
     *       @OA\Property(property="phone_number", type="string", example="+7(123)4567890"),
     *       @OA\Property(property="id_city", type="int", example="1"),
     *    ),),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     *     @OA\Response(
     *         response="402",
     *         description="Incorrect code",
     *     ),
     * )
     */


    public function changeUserInfo(Request $request)
    {

        $props = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'string|',
            'email' => 'email|',
            'phone_number' => 'string|',
            'id_city' => 'integer|',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }

        $user = User::find(auth('api')->user()->id);
        if (isset($props['email']) && ($props['email'] != (string)$user->email)) {
            $user->email_verified_at = null;
        }
        $user->Update($props);

        return $this->userInfo($user);
    }
    public function userInfo(User $user)
    {
        $reviews = reviewModel::where('id_user_owner', $user->id)->get();
        return response()->json([
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
            'status' => $user->status,
        ]);
    }


    public function deleteAccount(Request $request)
    {
        $id_user = auth('api')->user()->id;

        $user = User::where('id', $id_user)->first();

        Storage::disk("local")->delete('public/IN_GOOD_HANDS/' . $id_user);
        //Storage::disk("google")->delete('IN_GOOD_HANDS/'.$id_user);
        $user->delete();

        AddressModel::where('id_user', $id_user)->delete();
        return response()->json('account was deleted', 200);
    }




    public function generatePosts()
    {


        $category = rand(1,3);
        
        $props = [
            'title' => 'Леха в горном',
            'description' => 'Это Леха в горном',
            'id_category' => 1,
            'image_set' => [
                config('photo.generatePhoto'),
                config('photo.generatePhoto'),
            ],
            "address" =>  [
                "title" =>  "г Барнаул, Малый Прудской пер, д 46, кв 34",
                "longitude" =>  83.7543103,
                "latitude" =>  53.3285879
            ],
            'id_city' => 1,
        ];
        $props['address']['latitude'] = (float)$props['address']['latitude'];
        $props['address']['longitude'] = (float)$props['address']['longitude'];
        $props['created_at'] = Carbon::now();
        $props['updated_at'] = Carbon::now();
        $props['id_user'] = 2;
        $props['status'] = ('active');
        $props['address'] = json_encode($props['address']);

        for ($i = 0; $i < 10; $i++) {
            $category = rand(1,3);
            $props['id_category'] = $category;
            PostService::newPost($props);
        }

        return;
    }


    public function deleteDirsWithoutFiles()
    {
        $folders = Storage::disk("local")->allDirectories('PHOTOS');

        // print_r($folders);
        // return;
        foreach ($folders as $folder) {
            
            //$folder = $folders[6];
            
            $dir = explode('/',$folder);
            if(isset($dir[2]))
            {
                if(!postModel::find($dir[2]) || count(Storage::disk("local")->allFiles($folder)) == 0)
                {
                    Storage::disk("local")->deleteDirectory($folder);
                }

                
            }
            else{
                if(!User::find($dir[1]))
                {
                    Storage::disk("local")->deleteDirectory($folder);
                } 
            }
        }
    }

    public function deletePostsWithoutFiles()
    {
        $posts = postModel::select('id', 'id_user')->get();

        foreach ($posts as $post) {
            $id = $post->id;
            $id_user = $post->id_user;
            
            
            $files = Storage::disk("local")->allFiles("PHOTOS/$id/$id_user");
            if (count($files) == 0) {
                $post->delete();
            }
        }
    }
}
