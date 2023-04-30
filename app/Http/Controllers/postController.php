<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\postModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\postFilterRequest;
use App\Models\AddressModel;
use App\Models\CategoryModel;
use App\Models\CityModel;
use App\Models\favoritePost;
use App\Models\postStatus;
use App\Models\savedContacts;
use App\Service\UserService;
use App\Service\PostService;
use App\Service\Validation\ValitationService;
use phpseclib3\Crypt\EC\Curves\prime192v1;

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

        $this->validator->set($request->all(), [
            'title' => 'required|string|between:1,50',
            'id_category' => 'required',
            'image_set' => 'required|between:1,5',
            'address' => 'required|size:3',
            'id_city' => 'required',
            'show_email' => 'required|boolean'
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        $this->validator->set($request->input('address'), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'title' => 'required|string',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }



        $props = $request->all();
        $props['address']['latitude'] = (float)$props['address']['latitude'];
        $props['address']['longitude'] = (float)$props['address']['longitude'];
        $props['created_at'] = Carbon::now();
        $props['updated_at'] = Carbon::now();
        $props['id_user'] = $id;
        $props['status'] = 'active';
        $props['address'] = json_encode($props['address']);

        //Log::debug($props);

        $id_post = PostService::newPost($props);
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
        $props = $request->all();

        $this->validator->set($props, [
            'id_post' => 'required|integer',
            'title' => 'string|between:1,50',
            'id_category' => 'integer',
            'image_set' => 'between:1,5',
            'address' => 'size:3',
            'id_city' => 'integer',
            'show_email' => 'required|boolean',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        if (isset($props['address'])) {
            $this->validator->set($props['address'], [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'title' => 'required|string',
            ]);
            if (!$this->validator->validate()) {
                return response()->json($this->validator->errors, 400);
            }
            $props['address']['latitude'] = (float)$props['address']['latitude'];
            $props['address']['longitude'] = (float)$props['address']['longitude'];
        }


        $post = postModel::find($props['id_post']);
        if (!$post) {
            return response()->json(["message" => "post is missing"], 405);
        }

        if ($post->id_user != auth('api')->user()->id) {
            return response()->json(["message" => "There is not your post"], 405);
        }

        $updatable = (new postModel())->updatable;
        foreach ($props as $key => $prop) {
            //Подумать выкидывать исключение или сохранять
            if (!in_array($key, $updatable)) {
                unset($props[$key]);
            }
        }

        $post = PostService::updatePost($props, $post->id);

        return response()->json($post);
    }


    public function getPost(Request $request)
    {
        $id_post = $request->get('id_post');
        $post = postModel::find($id_post);
        $isMyPost = null;
        $contacts = null;
        if (auth('api')->user()) {
            if (savedContacts::where('id_post', $request->get('id_post'))
                ->where('id_user', auth('api')->user()->id)->first()
            ) {
                $id_contact_owner = User::find(postModel::find($id_post)->id_user)->id;

                $contacts = [
                    'phone' => User::find($id_contact_owner)->phone_number,
                    'address' => json_decode(postModel::find($id_post)->address),
                ];
                if ($post->show_email) {
                    $contacts['email'] = User::find($id_contact_owner)->email;
                }
            }

            if ($post->id_user == auth('api')->user()->id) {
                $isMyPost = true;
            }
        }

        $view_count = ($post->view_count);
        $post->view_count = ++$view_count;
        $post->save();

        if ($isMyPost) {
            $contacts = null;
        }
        return json_encode(PostService::getPostResponse($post->id, $contacts, $isMyPost));
    }



    public function similarPosts(Request $request)
    {
        $id_post = $request->get('id_post');
        //return $id_post;
        $posts = postModel::where('id', '!=', $id_post)->inRandomOrder()->limit(4)->get();
        return json_decode(PostService::getPostsWithPagination($posts, 4), true)["data"];
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
        $query = PostService::queryFilter(postModel::query(), $data);

        $posts = $query->get();
        $posts = $posts->where('status', 'active');


        if (isset($data['sort_by']) && $data['sort_by'] == 'distance') {
            if (isset($data['user_lat']) && isset($data['user_lon'])) {
                $postArr = PostService::sortPostsByDistance($posts, $data['user_lat'], $data['user_lon']);
                $posts = [];
                foreach ($postArr as $post) {
                    $posts[] = (object)$post;
                }

                $posts = collect($posts);
            }
        }


        return PostService::getPostsWithPagination($posts, $this->pagination);
    }

    public function userPostsData(Request $request)
    {

        $this->validator->set($request->all(), [
            'title' => 'string',
            'sort_type' => 'string',
            'status' => 'string',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $id = auth('api')->user()->id;
        $data = $request->all();

        $query = PostService::queryFilter(postModel::query(), $data);


        if (isset($data['status'])) {
            $query->where('status', $data['status']);
        }
        $posts = $query->orderBy('id', 'desc')->get();
        $posts = $posts->where('id_user', $id);

        $posts = json_decode($posts, true);
        $new = [];
        foreach ($posts as $post) {
            $post['like_count'] = favoritePost::where('id_post', $post['id'])->count();
            $new[] = (object)$post;
        }
        $posts = collect($new);
        return PostService::getPostsWithPagination($posts, $this->pagination, true);
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

        $response = json_decode($this->getPost($request), true)['post'];
        $response["address"] = json_decode($post->address);
        return  json_encode($response);
    }

    public function getContact(Request $request)
    {

        $id_post = $request->get('id_post');
        $id_user = postModel::find($id_post)->id_user;
        $phone_number = User::find($id_user)->phone_number;
        $address = postModel::find($id_post)->address;

        $loyalty_balanse = User::find(auth('api')->user()->id)->loyalty_balanse;
        if ($loyalty_balanse <= 0) {
            return response()->json(['Ваш рейтинг лояльности слишком низкий']);
        };


        if (!savedContacts::isContactsSaved(auth('api')->user()->id, $id_post)) {
            $loyalty_balanse--;
            $user = User::find(auth('api')->user()->id);
            $user->loyalty_balanse = $loyalty_balanse;
            $user->save();
            savedContacts::saveContacts(auth('api')->user()->id, $id_post);
        } else {
            return response()->json('Contact is already get');
        }
        return response()->json([
            "phone" => $phone_number,
            "address" => json_decode($address),
        ]);
    }

    public function getUserPosts(Request $request)
    {
        $posts = UserService::getAllUserPosts($request->get('id_user'));

        $postOnPage = $request->get('limit');
        if (!$postOnPage) {
            $postOnPage = $this->pagination;
        }
        return PostService::getPostsWithPagination($posts, $postOnPage);
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