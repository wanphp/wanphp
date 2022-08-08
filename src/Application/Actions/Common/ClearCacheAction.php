<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/21
 * Time: 16:34
 */

namespace App\Application\Actions\Common;


use App\Application\Actions\Action;
use Predis\ClientInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ClearCacheAction extends Action
{
  private ClientInterface $redis;

  public function __construct(LoggerInterface $logger, ClientInterface $redis)
  {
    $this->redis = $redis;
    parent::__construct($logger);
  }

  protected function action(): Response
  {
    $db = $this->args['db'] ?? 1;
    $this->redis->select($db);
    $this->redis->flushdb();
    return $this->respondWithData(['msg' => 'OK!']);
  }
}
