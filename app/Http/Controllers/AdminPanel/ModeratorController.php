<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CityModel;
use App\Models\CategoryModel;
use App\Models\postModel;
use App\Models\postStatus;
use App\Models\User;
use App\Models\moderationProcess;
use App\Service\PostService;
use App\Service\UserService;
use App\Jobs\pubishedPostJob;

use function Amp\Iterator\discard;

class ModeratorController extends Controller
{

    protected $pagination = 8;
    /** 
     * @OA\Get(
     *     path="/api/admin/get_pending_posts",
     *     summary="Получение списка постов ожидающих проверки модератора",
     *     tags={"Moderator"},
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=false,
     *       example = 1,
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
    public function getPendingPosts(Request $request)
    {
        $this->validator->set($request->all(), [
            'limit' => 'integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $prop = $request->all();
        $posts = postModel::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        $itemOnPage = isset($prop['limit']) ? $prop['limit'] : $this->pagination;
        return PostService::getPostsWithPagination($posts, $itemOnPage, true);
    }



    /** 
     * @OA\Get(
     *     path="/api/admin/get_review_posts",
     *     summary="Получение списка постов в проверке",
     *     tags={"Moderator"},
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=false,
     *       example = 1,
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
    public function getReviewPosts(Request $request)
    {
        $this->validator->set($request->all(), [
            'limit' => 'integer',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $prop = $request->all();
        $posts = postModel::where('status', 'review')->orderBy('created_at', 'desc')->get();
        $itemOnPage = isset($prop['limit']) ? $prop['limit'] : $this->pagination;
        return PostService::getPostsWithPagination($posts, $itemOnPage, true);
    }

    public function publishPost($id_post)
    {
        $post = postModel::find($id_post);
        $user = User::find($post->id_user);
        dispatch(new pubishedPostJob($user, $post));
        return PostService::changeStatus($id_post, 'active');
    }

    public function rejectPost($id_post, $reason)
    {
        $post = postModel::find($id_post);
        $user = User::find($post->id_user);
        dispatch(new pubishedPostJob($user, $post, $reason));
        return PostService::changeStatus($id_post, 'rejected');
    }


    /** 
     * @OA\Post(
     *     path="/api/admin/start_checking",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Начать проверку поста модератором",
     *     tags={"Moderator"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_post"},
     *       @OA\Property(property="id_post", type="int",example=84),
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
    public function startChecking(Request $request)
    {
        $id_user = auth('api')->user()->id;
        $this->validator->set($request->all(), [
            'id_post' => 'required|integer|exists:post,id',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }
        $props = $request->all();
        $props['id_user'] = $id_user;
        $post = postModel::find($props['id_post']);


        if ($checking = moderationProcess::where('id_post', $post->id)->where('id_user', '!=', $id_user)->orderBy('id', 'desc')->first()) {
            if (!$checking->result) {
                return response()->json('Пост уже проверяется другим модератором', 400);
            }
        }


        $post_data  = PostService::getPostResponse($post->id, null, true);
        $post_data['post']['address'] = $post_data['address'];
        if ($checking = moderationProcess::where('id_post', $post->id)->where('id_user', $id_user)->orderBy('id', 'desc')->first()) {
            if ($checking->result) {
                $checking = moderationProcess::Create($props);
            }
            $post->Update([
                'status' => 'review',
            ]);
            return response()->json([
                'checking_id' => $checking->id,
                'post' => $post_data['post'],
            ], 200);
        }

        $checking = moderationProcess::Create($props);
        $post->Update([
            'status' => 'review',
        ]);
        return response()->json([
            'checking_id' => $checking->id,
            'post' => $post_data['post'],
        ], 200);
    }


    /** 
     * @OA\Patch(
     *     path="/api/admin/end_checking",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Завершить проверку поста модератором",
     *     tags={"Moderator"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_checking","result"},
     *       @OA\Property(property="id_checking", type="int",example=20),
     *       @OA\Property(property="result", type="object",
     *                   required={"is_public","text"},
     *                   @OA\Property(property="is_public", type="boolean"),
     *                   @OA\Property(property="text", type="string")),
     *       ),
     *    ),
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
    public function endChecking(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_checking' => 'required|integer|exists:moderation_process,id',
            'result' => 'required|size:2',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        $this->validator->set($request->input('result'), [
            'is_public' => 'required|boolean',
            'text' => 'string',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }


        $props = $request->all();

        $checking = moderationProcess::find($props['id_checking']);
        $id_post = postModel::find($checking->id_post)->id;
        if ($props['result']['is_public']) {
            $response = $this->publishPost($id_post);
        } else if ($props['result']['text']) {
            $response = $this->rejectPost($id_post, $props['result']['text']);
        } else {
            $response = response()->json("Не отправлена причина отказа", 400);
        }

        $checking->Update([
            'result' => json_encode($props['result'])
        ]);

        return $response;
    }


    /** 
     * @OA\Patch(
     *     path="/api/admin/cancel_review",
     *     security={
     *           {"passport": {}},
     *      },    
     *     summary="Поменять статус поста на О\жидает проверки ",
     *     tags={"Moderator"},
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass user credentials",
     *     @OA\JsonContent(
     *       required={"id_checking"},
     *       @OA\Property(property="id_checking", type="int",example=20),
     * ),),
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
    public function cancelReview(Request $request)
    {
        $this->validator->set($request->all(), [
            'id_checking' => 'required|integer|exists:moderation_process,id',
        ]);
        if (!$this->validator->validate()) {
            return response()->json($this->validator->errors, 400);
        }

        $props = $request->all();

        $checking = moderationProcess::find($props['id_checking']);
        $post = postModel::find($checking->id_post);

        if ($post->status == 'review' && !$checking->result) {
            $post->status = 'pending';
            $post->save();
            $checking->delete();
            return PostService::getPostResponse($post->id);
        }
        
        return response()->json('Posts status is not review',400);
    }
}
