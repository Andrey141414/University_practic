<?php

namespace App\Http\Controllers;

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
use App\Service\UserService;
use App\Service\PostService;

class postController extends Controller
{

    protected $LocalPhotoPath = '/PHOTOS';
    protected $pagination = 10;
    /**
     * @OA\Post(
     *     path="/api/create_post",
     *     summary="Get list of blog posts",
     *     tags={"Posts"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"title","id_category","image_set","address","id_city"},
     *       @OA\Property(property="title", type="string", example="Пальто"),
     *       @OA\Property(property="id_category", type="int", example=1),
     *       @OA\Property(property="image_set", type="array",
     *       @OA\Items(type="string",
     *                 example={"The email field is required.","The email must be a valid email address."},
     *       ),),
     *       @OA\Property(property="address", type="boolean", example="true"),
     *       @OA\Property(property="id_city", type="boolean", example="true"),
     *    ),
     * ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized user",
     *     ),
     * )
     */
    public function createPost(Request $request)
    {
        $id = auth('api')->user()->id;
        //$post = (new postModel());
        //$id = 7;
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:1,50',
            'id_category' => 'required',
            'image_set' => 'required|between:1,5',
            'address' => 'required|size:3',
            'id_city' => 'required',
        ]);

        
        $validator1 = Validator::make($request->input('address'),[
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'title' => 'required|string',
        ]);
        
        if ($validator->fails() || $validator1->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }

        $address = $request->input('address');
        $address['latitude'] = (double)$address['latitude'];
        $address['longitude'] = (double)$address['longitude'];
        $post = PostModel::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'date' => Carbon::now(),
            'id_category' => $request->input('id_category'),
            'id_user' => $id,
            'address' => json_encode($address),
            'id_city' => $request->input('id_city'),
            'status' => statusModel::getStatusid('active'),
        ]);
        
        $id_post =  $post->id;

        $images = $request->input('image_set');
        
        Storage::disk("local")->makeDirectory($this->LocalPhotoPath.'/'. $id . '/' . $id_post);

//        Storage::disk("local")->makeDirectory('public/' . 'IN_GOOD_HANDS/' . $id . '/' . $id_post);

        //цикл
        foreach ($images as $key => $data) {
            $path = $this->LocalPhotoPath.'/' . $id . '/' . $id_post . '/' . $key . '.jpeg';
            $data = base64_decode($data);
            //Storage::disk("google")->put($path,$data);

            Storage::disk("local")->put($path, $data);
            //Storage::disk("local")->put('public/' . $path, $data);
        }
        //конец цикла

        $post->update([
            //'img_set_path' => 'IN_GOOD_HANDS/' . $id . '/' . $id_post,
            'img_set_path' => $this->LocalPhotoPath.'/'. $id . '/' . $id_post,
        ]); 
        $post->save();
        return response()->json(["message" => "Data was saved", "id_post" => $id_post], 200);
    }

    public function deletePost(Request $request)
    {

        $id_post = $request->get('id_post');
        $post = (new postModel())->where('id', $id_post)->first();
        if ($post == null) {
            return response()->json([
                "message" => "post is missing"
            ], 404);
        }

        $id_user = auth('api')->user()->id;
        if ($post->id_user != $id_user) {
            return response()->json([
                "message" => "There is no so address for you",
            ], 204);
        }

        $path = $post->img_set_path;
        $post->delete();
        //Storage::disk("local")->deleteDirectory('public/' . $path);
        Storage::disk("local")->deleteDirectory($path);
        
        return response()->json(["message" => "Data was deleted"], 200);
    }




    public function changePost(Request $request)
    {
        $id = auth('api')->user()->id;
        $id_post = $request->input('id_post');
        $post = (new postModel())->where('id', $id_post)->first();
        if ($post == null) {
            return response()->json([
                "message" => "post is missing"
            ], 204);
        }
        $id_user = auth('api')->user()->id;
        if ($post->id_user != $id_user) {
            return response()->json([
                "message" => "There is no so address for you",
            ], 204);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:1,50',
            'id_category' => 'required',
            'image_set' => 'required|between:1,5',
            'address' => 'required',
            'id_city' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error'
            ], 400);
        }


        $post->title = $request->input('title');
        $post->description = $request->input('description');
        $post->id_category = $request->input('id_category');
        $post->id_address = $request->input('id_address');


      
        $images = $request->input('image_set');
        //Storage::disk("local")->makeDirectory('public/' . 'IN_GOOD_HANDS/' . $id . '/' . $id_post);

        //цикл
        foreach ($images as $key => $data) {
            $path = 'IN_GOOD_HANDS/' . $id . '/' . $id_post . '/' . $key . '.jpeg';
            $data = base64_decode($data);
            Storage::disk("local")->put('public/' . $path, $data);
        }
        //конец цикла

        $post->img_set_path = 'IN_GOOD_HANDS/' . $id . '/' . $id_post;
        $post->save();
        return response()->json(["message" => "Data was saved"], 200);
    }


    public function getPost(Request $request)
    {
        $id_post = $request->get('id_post');
        $post = (new postModel())->where('id', $id_post)->first();

        $image_set = [];

        $view_count = ($post->view_count);
        $post->view_count = ++$view_count;

        //$path = 'public/IN_GOOD_HANDS/' . $post->id_user . '/' . $id_post;
        $path = $this->LocalPhotoPath .'/' . $post->id_user . '/' . $id_post;
        
        $images_path = Storage::disk("local")->files($path);
        foreach ($images_path as $key => $file) {
            //https://in-good-hands.dev.mind4.me/PHOTOS/8/65/0.jpeg
            $image_set[$key] = env('APP_DEV_URL').'/'.$file;// (Storage::url($file));
        }
        $post->save();
        return response()->json(PostService::getPostResponse($post->id,$image_set));
    }



    public function similarPosts(Request $request)
    {
        $id_post = $request->get('id_post');
        //return $id_post;
        $posts= postModel::where('id','!=',$id_post)->inRandomOrder()->limit(4)->get();
        return json_decode(PostService::getPostsWithPagination($posts,4),true)["data"];
    }


    public function favoritePostsCount()
    {
        $id_user = auth('api')->user()->id;
        $posts = postModel::where('id_user', $id_user)->get();

        $massiv = array();
        foreach ($posts as $key => $data) {
            $like_count = favoritePost::where('id_post', $data->id)->count();
            $mas = array(
                "like_count" => $like_count,
                "id_post" => $data->id
            );
            array_push($massiv, $mas);
        }
        return json_encode($massiv);
    }


    ////////////
    public function allPostsData(postFilterRequest $request)
    {

        $data = $request->all();
        // $request->validated();

        //echo(json_encode ($request->all()));
        $query = postModel::query();

        if (isset($data['id_category'])) {
            $query->where('id_category', $data['id_category']);
        }
        if (isset($data['id_city'])) {
            $id_addresses = (new AddressModel())->where('id_city', $data['id_city'])->pluck('id');
            $query->whereIn('id_address', $id_addresses);
            //
        }

        if (isset($data['title'])) {
            $query->where('title', 'like', "%{$data['title']}%");
        }

        if (isset($data['sort_type'])) {
            if ($data['sort_type'] == 'asc') {
                $query->orderBy($data['sort_by'], 'asc');
            }
            if ($data['sort_type'] == 'desc') {
                $query->orderBy($data['sort_by'], 'desc');
            }
        }

        
        $posts = $query->get();
        $posts = $posts->where('is_active');

        
        if (isset($data['sort_by']) && $data['sort_by'] == 'distance') {
            if (isset($data['user_lat']) && isset($data['user_lon'])){
                $postArr = PostService::sortPostsByDistance($posts,$data['user_lat'],$data['user_lon']);
                $posts = [];
                foreach($postArr as $post)
                {
                    $posts[] = (object)$post;
                }
                
                $posts = collect($posts);
            }
        }
        
        
        return PostService::getPostsWithPagination($posts,$this->pagination);
    }

    public function userPostsData(postFilterRequest $request)
    {
        $data = $request->validated();
        $query = postModel::query();
        $id = auth('api')->user()->id;

        if (isset($data['title'])) {
            $query->where('title', 'like', "%{$data['title']}%");
        }

        if (isset($data['sort_type'])) {
            if ($data['sort_type'] == 'asc') {
                $query->orderBy('date', 'asc');
            }
            if ($data['sort_type'] == 'desc') {
                $query->orderBy('date', 'desc');
            }
        }

        $posts = $query->orderBy('id', 'desc')->get();
        $posts = $posts->where('id_user', $id);
        return PostService::getPostsWithPagination($posts,$this->pagination);
    }
    ////////////


    public function getPhoneNumber(Request $request)
    {
        $id_post = $request->get('id_post');
        $id_user = (new postModel())->where('id', $id_post)->first()->id_user;
        $phone_number = (new User())->where('id', $id_user)->first()->phone_number;
        return $phone_number;
    }

    public function getAddress(Request $request)
    {
        $id_post = $request->get('id_post');
        $id_address = (new postModel())->where('id', $id_post)->first()->id_address;
        $address = (new AddressModel())->where('id', $id_address)->first()->title;
        return $address;
    }



    public function changePostActive(Request $request)
    {


        $id_post = $request->get('id_post');
        $post = (new postModel())->where('id', $id_post)->first();

        $id_user = auth('api')->user()->id;
        if ($post->id_user != $id_user) {
            return response()->json([
                "message" => "There is no so address for you",
            ], 204);
        }

        if ($post == false) {
            return response()->json(["message" => "There is no so post"], 404);
        }

        if ($post->is_active) {
            $is_active = false;
            $answer = json_encode(["message" => "Post is not active"]);
        } else {
            $is_active = true;
            $answer = json_encode(["message" => "Post is active"]);
        }

        $post->is_active = $is_active;
        $post->save();
        return $answer;
    }

    


    public function getPostForChange(Request $request)
    {


        $id_post = $request->get('id_post');
        $post = (new postModel())->where('id', $id_post)->first();
        $image_set = [];

        $id_user = auth('api')->user()->id;
        if ($post->id_user != $id_user) {
            return response()->json([
                "message" => "There is no so address for you",
            ], 204);
        }

        return $this->getPost($request);

        // $path = $this->LocalPhotoPath.'/' . $post->id_user . '/' . $id_post;
        // $images_path = Storage::disk("local")->files($path);
        // foreach ($images_path as $key => $file) {
        //     $image_set[$key] = env('APP_DEV_URL') . (Storage::url($file));
        // }
        // $post->save();
        // $user = (new User())->where('id', $post->id_user)->first();

        // $address = (new AddressModel())->where('id', $post->id_address)->first();


        // return response()->json(PostService::getPostResponse($post->id,$image_set));
    }

    public function getContact(Request $request)
    {
        $id_post = $request->get('id_post');
        $id_user = postModel::find($id_post)->id_user;
        $phone_number = User::find($id_user)->phone_number;
        $address = postModel::find($id_post)->address;
        return response()->json([
            "phone" => $phone_number,
            "address" => json_decode($address),
        ]);
    }

    public function getUserPosts(Request $request)
    {
        $posts = UserService::getAllUserPosts($request->get('id_user'));

        $postOnPage = $request->get('limit');
        if(!$postOnPage)
        {
            $postOnPage = $this->pagination;
        }
        return PostService::getPostsWithPagination($posts,$postOnPage);
    }
}




// public function loadPreviewToHeroku()
    // {
       
    //     $posts = (new postModel())::all();

    //     $paths = [];
    //     foreach($posts as  $key =>  $post)
    //     {
    //         $paths[$key] = 'IN_GOOD_HANDS/'.$post->id_user.'/'.$post->id;
    //     }
        
    //     foreach($paths as $key=>$path)
    //     {
            
    //         Storage::disk("local")->makeDirectory('public/'.$path);
    //         for($i = 0;$i < count(Storage::disk("google")->allFiles($path));$i++)
    //         {
                
    //             $content = Storage::disk("google")->get($path.'/'.$i.'.jpeg');
    //             Storage::disk("local")->put('public/'.$path.'/'.$i.'.jpeg',$content);
                
                
    //         }
    //     };
    //     $content = Storage::disk("google")->get('IN_GOOD_HANDS/is_exist.txt');
    //     Storage::disk("local")->put('public/IN_GOOD_HANDS/is_exist.txt',$content);
    //     return [Storage::disk("local")->allDirectories(),Storage::disk("local")->allFiles(),201];;
    // }