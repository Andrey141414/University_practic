<?php

namespace App\Service;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\postFilterRequest;
use App\Jobs\SavePhoto;
use App\Models\AddressModel;
use App\Models\CategoryModel;
use App\Models\CityModel;
use App\Models\favoritePost;
use App\Models\postStatus;
use App\Models\reservation;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
class PostService
{

    public $validatedDataCreate = [1, 2, 3];

    public static function getPostsWithPagination($posts, $pagination, $isMyposts = null)
    {
        $items_num = $pagination;

        $data = [];
        for ($i = 0; $i < $posts->paginate($items_num)->count(); $i++) {

            $paginate_post = $posts->paginate($items_num)->items()[$i];


            $image_set = [];
            $image_set[] = env('APP_DEV_URL') . $paginate_post->img_set_path . '/0.jpeg';
            $data[$i] = ([
                "id" => $paginate_post->id,
                "title" => $paginate_post->title,
                "description" => $paginate_post->description,
                "created_at" => $paginate_post->created_at  ? date('d-m-Y', strtotime($paginate_post->created_at)) : null,
                "updated_at" => $paginate_post->updated_at ? date('d-m-Y', strtotime($paginate_post->updated_at)) : null,
                "image_set" => $image_set,
                "view_count" => $paginate_post->view_count,
                "user" => UserService::getShortUserModel($paginate_post->id_user),
                "city" => CityModel::getCityModel($paginate_post->id_city),
                "category" => CategoryModel::getCategoryModel($paginate_post->id_category),
                "status" => $paginate_post->status,
            ]);

            if (isset($isMyposts)) {
                $data[$i]["address"] = json_decode($paginate_post->address);

                if ($paginate_post->status == 'reserved') {
                    $reservation = reservation::where('id_post' , $paginate_post->id)->first();
                    if(isset($reservation))
                    {
                        $data[$i]["reservation_data"] = ReservationService::getShortReservationModel($reservation->id);
                    }
                    
                }
            }

            if (isset($paginate_post->distance)) {
                $data[$i]['distance'] = $paginate_post->distance;
            }

            if (isset($paginate_post->like_count)) {
                $data[$i]['like_count'] = $paginate_post->like_count;
            }
        }

        $anwer = json_encode([
            "page" => $posts->paginate($items_num)->currentPage(),
            "per_page" => $posts->paginate($items_num)->count(), //perPage(),,
            "total" => $posts->paginate($items_num)->total(),
            "total_pages" => $posts->paginate($items_num)->lastPage(),
            "data" => $data,

        ]);


        return $anwer;
    }


    public static function getPostResponse($id_post, $contacts = null, $isMypost = null)
    {


        $post = postModel::find($id_post);

        $image_set = [];

        $path = $post->img_set_path;

        $images_path = Storage::disk("local")->files($path);
        foreach ($images_path as $key => $file) {
            $image_set[$key] = env('APP_DEV_URL') . '/' . $file;
        }
        $response['post'] = [
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'created_at' => date('d-m-Y', strtotime($post->created_at)),
            'updated_at' => date('d-m-Y', strtotime($post->updated_at)),
            'category' => CategoryModel::getCategoryModel($post->id_category),
            'image_set' => $image_set,
            'city' => CityModel::getCityModel($post->id_city),
            'view_count' => $post->view_count - 1,
            'status' => $post->status,
            'user' => UserService::getShortUserModel($post->id_user),
        ];

        

        if (isset($isMypost)) {
            $response['address'] = json_decode($post->address);
        }
        if (isset($contacts)) {
            $response['contacts'] = $contacts;
        }

        return  $response;
    }

    public static function sortPostsByDistance($posts, $userLat, $userLong)
    {
        //$distance = [];
        //$newPosts = [];

        $newPosts = json_decode($posts, true);
        $i = 0;
        foreach ($posts as $post) {
            $address = json_decode($post->address, true);
            $postLat = $address['latitude'];
            $postLon = $address['longitude'];
            $newPosts[$i]['distance'] = self::getDistanceBetweenPointsNew(
                $userLat,
                $userLong,
                $postLat,
                $postLon
            );
            $i++;
        }

        $throw = null;
        try {
            for ($j = 0; $j < count($newPosts) - 1; $j++) {
                for ($i = 0; $i < count($newPosts) - $j - 1; $i++) {
                    // если текущий элемент больше следующего
                    //print_r($newPosts[$i]);
                    $throw = $newPosts[$i];
                    $throw = $newPosts[$i + 1];
                    if ($newPosts[$i]['distance'] > $newPosts[$i + 1]['distance']) {
                        // меняем местами элементы
                        $tmp_var = $newPosts[$i + 1];
                        $newPosts[$i + 1] = $newPosts[$i];
                        $newPosts[$i] = $tmp_var;
                    }
                }
            }
        } catch (\Throwable $th) {
            print_r($throw);
        }

        //sort($distance);
        return $newPosts;
    }

    static function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'kilometers')
    {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        switch ($unit) {
            case 'miles':
                break;
            case 'kilometers':
                $distance = $distance * 1.609344;
        }
        return (round($distance, 2));
    }

    function savePhoto($images, $folder)
    {

        // dispatch(new SavePhoto($images, $folder));
        // return;
         foreach ($images as $key => $data) {
            $path =  $folder . '/' . $key . '.jpeg';
            
            Log::debug($path);
            
            $data = base64_decode($data);
            $img = Image::make($data)
            ->resize(1024, 1024);

            Storage::disk("local")->put($path,$img->encode('jpeg'));
        }
    }

    static function updatePost($props, $id_post)
    {
        $images = null;
        if (isset($props['image_set'])) {
            $images = $props['image_set'];
            unset($props['image_set']);
        }
        $post = postModel::find($id_post);
        $post->Update($props);
        $post->save();
        if ($images) {
            $postPhotoFolder = config('photo.localPhotoPath') . '/' . $post->id_user . '/' . $post->id;
            Storage::disk("local")->deleteDirectory($postPhotoFolder);
            (new PostService)->savePhoto($images, $postPhotoFolder);
            //self::savePhoto($images, config('photo.localPhotoPath') . '/' . $post->id_user . '/' . $id_post);
        }

        return $post;
    }

    static function newPost($props)
    {
        $images = $props['image_set'];
        unset($props['image_set']);
        $post = postModel::create($props);

        $postPhotoFolder = config('photo.localPhotoPath') . '/' . $post->id_user . '/' . $post->id;



        Storage::disk("local")->makeDirectory($postPhotoFolder, '777');
        (new PostService)->savePhoto($images, $postPhotoFolder);


        $post->update([
            'img_set_path' => $postPhotoFolder,
        ]);
        $post->save();

        return $post->id;
    }


    public static function queryFilter($query,$data,$extention = null)
    {
        if (isset($data['id_category'])) {
            $query->where('id_category', $data['id_category']);
        }
        if (isset($data['id_city'])) {
            $query->where('id_city', $data['id_city']);
        }

        if (isset($data['title'])) {
            $query->where('title', 'like', "%{$data['title']}%");
        }


        if (isset($data['sort_by']) && $data['sort_by'] == 'date') {
            $data['sort_by'] = 'created_at';
        }

        if(!isset($data['sort_by']))
        {
            $data['sort_by'] = 'id';
        } 
        if (isset($data['sort_type'])) {
            if ($data['sort_type'] == 'asc') {
                $query->orderBy($data['sort_by'], 'asc');
            }
            if ($data['sort_type'] == 'desc') {
                $query->orderBy($data['sort_by'], 'desc');
            }
        }


        if($extention)
        {
            if(isset($data['id_user'])) {
                $query->where('id_user', $data['id_user']);
            }

            if(isset($data['id_ad'])) {
                $query->where('id', $data['id_ad']);
            }
        }
        return $query;
    }
    
    
}



// public function GetPosts(mixed $posts)
// {

//     //Имя ID Дата регистрации
//     $items_num = 10;

//     $data = [];
//     for ($i = 0; $i < $posts->paginate(10)->count(); $i++) {

//         $paginate_post = $posts->paginate($items_num)->items()[$i];
//         $user = User::find($paginate_post->id_user);
//         $image_set = [];
//         $image_set[] = env('APP_DEV_URL') . '/storage' . '/' . $paginate_post->img_set_path . '/0.jpeg';
//         $data[$i] = ([
//             "id" => $paginate_post->id,
//             "title" => $paginate_post->title,
//             "description" => $paginate_post->description,
//             "date" => date('d-m-Y', strtotime($paginate_post->date)),
//             "image_set" => $image_set,
//             "view_count" => $paginate_post->view_count,
//             "user" => [
//                 "id" => $paginate_post->id_user,
//                 "name" => $user->name,
//                 "created_at" => date('d-m-Y', strtotime($user->created_at)),
//             ],
//             "city_title" => CityModel::where('id', $paginate_post->id_city)->first()->name,
//             "category_title" => CategoryModel::where('id', $paginate_post->id_category)->first()->name,
//             "address" => json_decode($paginate_post->address),
//             "status" => postStatus::getStatusName($paginate_post->status),
//         ]);
//     }

//     $anwer = json_encode([
//         "page" => $posts->paginate($items_num)->currentPage(),
//         "per_page" => $posts->paginate($items_num)->count(), //perPage(),,
//         "total" => $posts->paginate($items_num)->total(),
//         "total_pages" => $posts->paginate($items_num)->lastPage(),
//         "data" => $data,

//     ]);


//     return $anwer;
// }