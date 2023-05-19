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
use App\Service\HelpService;

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
                "created_at" => $paginate_post->created_at  ? HelpService::formatDate($paginate_post->created_at) : null,
                "updated_at" => $paginate_post->updated_at ? HelpService::formatDate($paginate_post->updated_at) : null,
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
                    $reservation = reservation::where('id_post', $paginate_post->id)->first();
                    if (isset($reservation)) {
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
            'created_at' => HelpService::formatDate($post->created_at),
            'updated_at' => HelpService::formatDate($post->updated_at),
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
            $response['contacts'] = [
                'phone' => User::find($post->id_user)->phone_number,
                'address' => json_decode(postModel::find($post->id)->address),
            ];
            if ($post->show_email) {
                $response['contacts']['email'] = User::find($post->id_user)->email;
            }
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

    function savePhoto($images, $folder, $crop)
    {

        if ($crop) {
            foreach ($images as $key => $data) {
                $path =  $folder . '/' . $key . '.jpeg';
                $data = base64_decode($data);
                $img = Image::make($data)
                    ->resize($crop['length'], $crop['width']);
                Storage::disk("local")->put($path, $img->encode('jpeg'));
            }
        } else {
            foreach ($images as $key => $data) {
                $path = $folder . '/' . $key . '.jpeg';
                $data = base64_decode($data);
                Storage::disk("local")->put($path, $data, 'public');
            }
        }
    }

    static function updatePost($props, $id_post)
    {
        $images = null;
        $crop = null;
        if (isset($props['image_set'])) {
            $images = $props['image_set'];
            unset($props['image_set']);
        }
        if (isset($props['crop'])) {
            $crop = $props['crop'];
            unset($props['crop']);
        }
        $post = postModel::find($id_post);
        $post->Update($props);
        $post->save();
        if ($images) {
            $postPhotoFolder = config('photo.localPhotoPath') . '/' . $post->id_user . '/' . $post->id;
            Storage::disk("local")->deleteDirectory($postPhotoFolder);
            Storage::disk("local")->makeDirectory($postPhotoFolder, '777');
            (new PostService)->savePhoto($images, $postPhotoFolder, $crop);
            //self::savePhoto($images, config('photo.localPhotoPath') . '/' . $post->id_user . '/' . $id_post);
        }

        return $post;
    }

    static function newPost($props)
    {
        $images = $props['image_set'];
        $crop = null;
        unset($props['image_set']);
        if (isset($props['crop'])) {
            $crop = $props['crop'];
            unset($props['crop']);
        }
        $post = postModel::create($props);

        $postPhotoFolder = config('photo.localPhotoPath') . '/' . $post->id_user . '/' . $post->id;



        Storage::disk("local")->makeDirectory($postPhotoFolder, '777');
        (new PostService)->savePhoto($images, $postPhotoFolder, $crop);


        $post->update([
            'img_set_path' => $postPhotoFolder,
        ]);
        $post->save();

        return $post->id;
    }


    public static function queryFilter($query, $data, $extention = null, $onlyActive = null)
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

        if (!isset($data['sort_by'])) {
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


        if ($extention) {
            if (isset($data['id_user'])) {
                $query->where('id_user', $data['id_user']);
            }

            if (isset($data['id_ad'])) {
                $query->where('id', $data['id_ad']);
            }
        }

        if ($onlyActive) {
            $query->whereIn('id_category', function ($_query) {
                $_query->select('id')
                    ->from('category')
                    ->where('category.is_active', '1')
                    ->get();
            });

            $query->whereIn('id_city', function ($_query) {
                $_query->select('id')
                    ->from('city')
                    ->where('city.is_active', '1')
                    ->get();
            });
        }
        return $query;
    }

    public static function changeStatus($id_post, $status)
    {
        $post = postModel::find($id_post);
        $post->Update([
            'status' => $status
        ]);

        return PostService::getPostResponse($post->id);
    }

    public static function sortTitlesByLevenshtein($word, $posts, $limit)
    {
        $levenshteinArr = [];
        $ids = [];
        foreach ($posts as $post) {
            $levenshteinArr[] = levenshtein($word, $post->title);
            $ids[] = $post->id;
        }

        for ($j = 0; $j < count($levenshteinArr) - 1; $j++) {
            for ($i = 0; $i < count($levenshteinArr) - $j - 1; $i++) {
                if ($levenshteinArr[$i] > $levenshteinArr[$i + 1]) {
                    // меняем местами элементы
                    $tmp_var = $levenshteinArr[$i + 1];
                    $levenshteinArr[$i + 1] = $levenshteinArr[$i];
                    $levenshteinArr[$i] = $tmp_var;


                    $tmp_var = $ids[$i + 1];
                    $ids[$i + 1] = $ids[$i];
                    $ids[$i] = $tmp_var;
                }
            }
        }

        return array_slice($levenshteinArr,0,$limit) ;
    }
}
