<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/28
 * Time: 9:46
 */

namespace App\Application\Api\Common;


use App\Application\Api\Api;
use App\Domain\Common\RouterInterface;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use ReflectionClass;

class SyncRouterApi extends Api
{
  private RouterInterface $router;

  public function __construct(RouterInterface $router)
  {
    $this->router = $router;
  }

  /**
   * @return Response
   * @throws Exception
   * @OA\Get(
   *  path="/api/manage/syncrouter",
   *  tags={"System"},
   *  summary="同步路由",
   *  operationId="SyncNavigate",
   *  security={{"bearerAuth":{}}},
   *  @OA\Response(response="200",description="请求成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'GET':
        //数据库内的操作
        $routes = $this->router->select('id,callable');
        $routes = array_column($routes, 'callable', 'id');
        //现有操作
        $current_actions = [];
        $files = array_merge(
          glob(realpath(__DIR__ . '/../../Actions') . '/*/*.php'),
          glob(realpath(__DIR__ . '/../Manage') . '/*.php'),
          glob(realpath(__DIR__ . '/../Manage') . '/*/*.php'),
          glob(realpath('../wanphp/plugins') . "/*/src/Application/Manage/*.php") //插件操作
        );
        $stack = [];

        //系统控制器
        if (!empty($files)) foreach ($files as $file) {
          if (is_file($file)) {
            $action = str_replace(
              [realpath('../') . '/src', realpath('../') . '/', '/src', '.php'],
              ['App', '', '', ''],
              $file);
            $arr = explode('/', $action);
            array_walk_recursive($arr, function (&$value) {
              $value = ucfirst($value);
            });
            $action = join('\\', $arr);
            $rc = new ReflectionClass($action); //建立实体类的反射类

            $docblock = $rc->getDocComment();
            if ($docblock) {
              $current_actions[] = $rc->getName();
              preg_match('/@title\s(.*?)\s\*/s', $docblock, $title);
              $title = isset($title[1]) ? trim($title[1]) : '';
              preg_match("/@route\s(.*?)\s\*/s", $docblock, $matches);
              $route = isset($matches[1]) ? trim($matches[1]) : '';

              $data = [
                'name' => $title,
                'route' => $route,
                'callable' => $rc->getName()
              ];
              if (in_array($rc->getName(), $routes)) {//更新
                $this->router->update($data, ['id' => array_search($rc->getName(), $routes)]);
              } else {//新增
                $stack[] = $data;
              }
            }
          }
        }
        //删除授权操作，即无须授权即可操作
        $delActions = array_diff($routes, $current_actions);
        if (count($delActions) > 0) {
          $this->router->delete(['id' => array_keys($delActions)]);
        }
        //新增授权操作
        if (count($stack) > 0) {
          $this->router->insert($stack);
        }

        $routes = $this->router->select('id,navId,name,route', ['ORDER' => ['sortOrder' => 'ASC']]);
        return $this->respondWithData(['routes' => $routes ?? []]);
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}
