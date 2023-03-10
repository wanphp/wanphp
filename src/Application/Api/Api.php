<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/25
 * Time: 10:49
 */

namespace App\Application\Api;

/**
 * @OA\Info(
 *     description="WanPHP 系统快速开发基础接口",
 *     version="1.1.0",
 *     title="系统开发基础接口"
 * )
 * @OA\Server(
 *   description="OpenApi host",
 *   url="https://api.wanphp.com"
 * )
 */

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="认证授权,获取访问令牌"
 * )
 * @OA\Tag(
 *     name="UserRole",
 *     description="用户角色",
 * )
 * @OA\Tag(
 *     name="User",
 *     description="用户操作接口",
 * )
 * @OA\Tag(
 *     name="Clients",
 *     description="客户端",
 * )
 * @OA\Tag(
 *     name="AdminRole",
 *     description="管理员角色",
 * )
 * @OA\Tag(
 *     name="Admin",
 *     description="系统管理员",
 * )
 * @OA\Tag(
 *     name="System",
 *     description="系统管理",
 * )
 */

/**
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 * )
 * @OA\Schema(
 *   title="出错提示",
 *   schema="Error",
 *   type="object"
 * )
 * @OA\Schema(
 *   title="成功提示",
 *   schema="Success",
 *   type="object"
 * )
 */

use Wanphp\Libray\Slim\Action;

abstract class Api extends Action
{

  protected function thumb(string $image, string $size): string
  {
    $info = pathinfo($image);
    return str_replace('/image/', '/image/thumb/', $info['dirname']) . "/{$info['filename']}/{$size}.{$info['extension']}";
  }
}
