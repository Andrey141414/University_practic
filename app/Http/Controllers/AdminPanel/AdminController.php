<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\userController;
use Illuminate\Http\Request;
use App\Models\CityModel;
use App\Models\CategoryModel;
use App\Models\postModel;
use App\Models\postStatus;
use App\Models\User;
use App\Service\PostService;
use App\Service\UserService;
use Illuminate\Support\Facades\DB;
class AdminController extends Controller
{

    protected $pagination = 8;
    //Города

    /** 
     * @OA\Get(
     *     path="/api/admin/all_cities",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Получение списка городов",
     *     tags={"Administrator"},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
     */
    public function getCities()
    {
        return response()->json(CityModel::all(), 200);
    }
    /** 
     * @OA\Post(
     *     path="/api/admin/create_city",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Создание города",
     *     tags={"Administrator"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"name","is_active"},
     *       @OA\Property(property="name", type="string",example="Вологда"),
     *       @OA\Property(property="is_active", type="bool",example=true),
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
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
     */

    public function createCity(Request $request)
    {
        $this->validator->set($request->all(), [
            'name' => 'required|string|unique:city',
            'is_active' => 'required|boolean',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $props = $request->all();
        $city = CityModel::Create($props);
        return response()->json($city, 200);
    }

    /** 
     * @OA\Delete(
     *     path="/api/admin/delete_city",
     *     summary="Удаление города",
     *     security={
     *           {"passport": {}},
     *      },         *     
     *     tags={"Administrator"},
     *     @OA\Parameter(
     *       name="id_city",
     *       in="query",
     *       required=true,
     *       example = 9,
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
    public function deleteCity(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_city' => 'required|integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        $id_city = $request->all()['id_city'];
        $city = CityModel::find($id_city);
        if ($city) {
            $city->delete();
            return response()->json('city was deleted', 200);
        }
        return response()->json('нет такого города', 404);
    }

    /** 
     * @OA\Patch(
     *     path="/api/admin/change_city",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Изменение города",
     *     tags={"Administrator"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_city","name","is_active"},
     *       @OA\Property(property="id_city", type="int"),
     *       @OA\Property(property="name", type="string",example="Вологда"),
     *       @OA\Property(property="is_active", type="bool",example=true),
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
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
     */
    public function changeCity(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_city' => 'required|integer',
            'name' => 'string|unique:city',
            'is_active' => 'boolean',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $props = $request->all();
        $city = CityModel::find($props['id_city']);
        if ($city) {
            $city->Update($props);
        }
        return response()->json($city, 200);
    }

    //Категории

    /** 
     * @OA\Get(
     *     path="/api/admin/all_categories",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Получение списка категорий",
     *     tags={"Administrator"},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
     */
    public function getCategories()
    {
        return response()->json(categoryModel::all(), 200);
    }


    /** 
     * @OA\Post(
     *     path="/api/admin/create_category",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Создание категории",
     *     tags={"Administrator"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"icon","name","is_active"},
     *       @OA\Property(property="name", type="string",example="Новое название"),
     *       @OA\Property(property="icon", type="string",example="👕"),
     *       @OA\Property(property="is_active", type="bool",example=true),
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
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
     */
    public function createCategory(Request $request)
    {
        $this->validator->set($request->all(), [
            'name' => 'required|string|unique:category',
            'icon' => 'required|string|between:1,2',
            'is_active' => 'required|boolean',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $props = $request->all();
        $category = CategoryModel::Create($props);
        return response()->json($category, 200);
    }

    /** 
     * @OA\Delete(
     *     path="/api/admin/delete_category",
     *     summary="Удаление категории",
     *     security={
     *           {"passport": {}},
     *      },         *     
     *     tags={"Administrator"},
     *     @OA\Parameter(
     *       name="id_category",
     *       in="query",
     *       required=true,
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
    public function deleteCategory(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_category' => 'required|integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        $id_category = $request->all()['id_category'];
        $category = CategoryModel::find($id_category);
        if ($category) {
            $category->delete();
            return response()->json('category was deleted', 200);
        }
        return response()->json('нет такой категории', 404);
    }

    /** 
     * @OA\Patch(
     *     path="/api/admin/change_category",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Изменение категории",
     *     tags={"Administrator"},
     *     @OA\RequestBody(
     *     required=false,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_category","icon","name","is_active"},
     *       @OA\Property(property="id_category", type="int"),
     *       @OA\Property(property="name", type="string",example="Новое название"),
     *       @OA\Property(property="icon", type="string",example="👕"),
     *       @OA\Property(property="is_active", type="bool",example=true),
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
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
     */
    public function changeCategory(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_category' => 'required|integer',
            'name' => 'string|unique:category',
            'is_active' => 'boolean',
            'icon' => 'string|between:1,2|unique:category',
        ]);

        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }


        $props = $request->all();


        $category = CategoryModel::find($props['id_category']);
        if ($category) {
            $category->Update($props);
        }
        return response()->json($category, 200);
    }

    /** 
     * @OA\Get(
     *     path="/api/admin/get_all_users",
     *     summary="Получение списка пользователей",
     *     tags={"Administrator"},
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=false,
     *       example = 1,
     *       ),
     *     @OA\Parameter(
     *       name="id_user",
     *       in="query",
     *       required=false,
     *       example = 123,
     *       ),
     *     @OA\Parameter(
     *       name="email",
     *       in="query",
     *       required=false,
     *       example = "user@mail.com",
     *       ),     
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     * )
     */
    public function getUsers(Request $request)
    {
        $this->validator->set($request->all(), [
            'limit' => 'integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $prop = $request->all();
        $query = UserService::queryFilter(User::query(), $prop);
        $users = $query->get();
        $itemOnPage = isset($prop['limit']) ? $prop['limit'] : $this->pagination;
        return (new UserService())->getUsersWithPagination($users, $itemOnPage);
    }

    /** 
     * @OA\Get(
     *     path="/api/admin/get_all_posts",
     *     summary="Получение списка постов ",
     *     tags={"Administrator"},
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=false,
     *       example = 1,
     *       ),
     *     @OA\Parameter(
     *       name="id_user",
     *       in="query",
     *       required=false,
     *       example = 2,
     *       ),
     *      @OA\Parameter(
     *       name="id_city",
     *       in="query",
     *       required=false,
     *       example = 2,
     *       ),
     *      @OA\Parameter(
     *       name="id_category",
     *       in="query",
     *       required=false,
     *       example = 2,
     *       ),
     *      @OA\Parameter(
     *       name="title",
     *       in="query",
     *       required=false,
     *       example = "Еда",
     *       ),
     *      @OA\Parameter(
     *       name="sort_type",
     *       in="query",
     *       required=false,
     *       example = "asc",
     *       ),
     *      @OA\Parameter(
     *       name="sort_by",
     *       in="query",
     *       required=false,
     *       example = "date",
     *       ),
     *      @OA\Parameter(
     *       name="id_ad",
     *       in="query",
     *       required=false,
     *       example = 123,
     *       ),
     *    @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     * )
     */
    public function getPosts(Request $request)
    {
        $this->validator->set($request->all(), [
            'limit' => 'integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $prop = $request->all();
        $query = PostService::queryFilter(postModel::query(), $prop, true);
        $posts = $query->get();
        $itemOnPage = isset($prop['limit']) ? $prop['limit'] : $this->pagination;
        return PostService::getPostsWithPagination($posts, $itemOnPage, true);
    }


    /** 
     * @OA\Post(
     *     path="/api/admin/change_user_status",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Изменение статуса пользователя",
     *     tags={"Administrator"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_user","status"},
     *       @OA\Property(property="id_user", type="int",example=2),
     *       @OA\Property(property="status", type="string",example="banned"),
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
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
     */
    public function changeUserStatus(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_user' => 'required|integer',
            'status' => 'required|string|exists:user_status,raw_value',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $user = User::find($request->input('id_user'));
        $user->Update([
            'status' => $request->input('status')
        ]);

        return (new userController())->userInfo($user);
    }




    /** 
     * @OA\Post(
     *     path="/api/admin/change_post_status",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Изменение статуса пользователя",
     *     tags={"Administrator"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_post","status"},
     *       @OA\Property(property="id_post", type="int",example=2),
     *       @OA\Property(property="status", type="string",example="banned"),
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
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
     */
    public function changePostStatus(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_post' => 'required|integer',
            'status' => 'required|string|exists:post_status,raw_value',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        
        return PostService::changeStatus($request->input('id_post'),$request->input('status'));
    }


/** 
     * @OA\Get(
     *     path="/api/admin/get_post_statusses",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Получение списка статусов поста",
     *     tags={"Administrator"},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
*/
    public function getPostStatuses()
    {
        return response()->json(postStatus::all());
    }


/** 
     * @OA\Get(
     *     path="/api/admin/get_user_statusses",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Получение списка статусов пользователя",
     *     tags={"Administrator"},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="string",         
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Validation error",
     *     ),
     * ),
*/
    public function getUserStatuses()
    {
        return response()->json(DB::table('user_status')->get());
    }

    public function changeUserRole(Request $request)
    {
        
    }

}
