<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/21
 * Time: 16:34
 */

namespace App\Application\Actions\Common;


use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\CacheInterface;

class ClearCacheAction extends Action
{
  private CacheInterface $cache;

  public function __construct(LoggerInterface $logger, CacheInterface $cache)
  {
    $this->cache = $cache;
    parent::__construct($logger);
  }

  protected function action(): Response
  {
    return $this->respondWithData(['msg' => '清除记录' . $this->cache->clear() . '!']);
  }
}
