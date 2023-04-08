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
class PostService
{
    
    public static function getPostsWithPagination($posts,$pagination)
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
                  "date" => date('d-m-Y', strtotime($paginate_post->date)),
                  "image_set" => $image_set,
                  "view_count" => $paginate_post->view_count,
                  "user" => UserService::getShortUserModel($paginate_post->id_user),
                  "city" => CityModel::getCityModel($paginate_post->id_city),
                  "category" => CategoryModel::getCategoryModel($paginate_post->id_category),
                  "address" => json_decode($paginate_post->address),
                  "status" => statusModel::getStatusName($paginate_post->status),
              ]);

              
              if(isset($paginate_post->distance))
              {
                $data[$i]['distance'] = $paginate_post->distance;
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
    

    public static function getPostResponse($id_post, $image_set)
    {
        $post = postModel::find($id_post);
        return [
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'date' => date('d-m-Y', strtotime($post->date)),
            'category' => CategoryModel::getCategoryModel($post->id_category),
            'image_set' => $image_set,
            'city' => CityModel::getCityModel($post->id_city),
            'view_count' => $post->view_count - 1,
            'address' => json_decode($post->address),
            'status' => StatusModel::getStatusName($post->status),
            'user' => UserService::getShortUserModel($post->id_user),
        ];
    }

    public static function sortPostsByDistance($posts,$userLat,$userLong)
    {
        //$distance = [];
        //$newPosts = [];
        
        $newPosts = json_decode($posts,true); 
        $i = 0;
        foreach($posts as $post)
        {
            $address = json_decode($post->address,true);
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

        for ($j = 0; $j < count($newPosts) - 1; $j++){
            for ($i = 0; $i < count($newPosts) - $j - 1; $i++){
                // если текущий элемент больше следующего
                if ($newPosts[$i]['distance'] > $newPosts[$i + 1]['distance']){
                    // меняем местами элементы
                    $tmp_var = $newPosts[$i + 1];
                    $newPosts[$i + 1] = $newPosts[$i];
                    $newPosts[$i] = $tmp_var;
                }
            }
        }
        //sort($distance);
        return $newPosts;
    } 

    static function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'kilometers') {
        $theta = $longitude1 - $longitude2; 
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
        $distance = acos($distance); 
        $distance = rad2deg($distance); 
        $distance = $distance * 60 * 1.1515; 
        switch($unit) { 
          case 'miles': 
            break; 
          case 'kilometers' : 
            $distance = $distance * 1.609344; 
        } 
        return (round($distance,2)); 
      }

      function cmp($a, $b) {
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
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
//             "status" => statusModel::getStatusName($paginate_post->status),
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