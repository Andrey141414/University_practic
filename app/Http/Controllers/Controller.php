<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Swagger документация проекта <<В добрые руки>>",
 *      description="Open API",
 *      @OA\Contact(
 *          email="admin@admin.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\PathItem(
 *      path="/",
 * )
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description=" API Server"
 * )

 *
 * @OA\Tag(
 *     name="Projects",
 *     description=" Api Endpoints"
 * )
 * @OA\Schemes(
 *   format="http")
 * @OA\SecurityScheme(
 *      securityScheme="bearer_token",
 *      type="http",
 *      scheme="bearer"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
{
    ini_set('max_execution_time', 300);
    ini_set('max_input_time', 600);
}

}
