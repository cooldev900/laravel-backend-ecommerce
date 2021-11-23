<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

abstract class ApiController extends Controller
{
    /**
     * @OA\OpenApi(
     *   @OA\Server(
     *      url="/api"
     *   ),
     *   @OA\Info(
     *      title="Omni Portal API Document",
     *      version="1.0.0",
     *   ),
     * )
     */

    /**
     *@OA\Tag(name="UnAuthorize", description="No user login required")
     */

    /**
     *@OA\Tag(name="User", description="User management with authentication")
     */

    /**
     * @OA\SecurityScheme(
     *       scheme="Bearer",
     *       securityScheme="Bearer",
     *       type="apiKey",
     *       in="header",
     *       name="Authorization",
     * )
     */
}