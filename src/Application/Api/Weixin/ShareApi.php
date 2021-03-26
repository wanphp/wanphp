<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2021/3/8
 * Time: 10:24
 */

namespace App\Application\Api\Weixin;


use App\Application\Api\Api;
use App\Infrastructure\Weixin\WeChatBase;
use Psr\Http\Message\ResponseInterface as Response;

class ShareApi extends Api
{
  private $weChatBase;

  public function __construct(WeChatBase $weChatBase)
  {
    $this->weChatBase = $weChatBase;
  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    $data = $this->request->getParsedBody();
    return $this->respondWithData($this->weChatBase->getSignPackage($data['url']));
  }
}