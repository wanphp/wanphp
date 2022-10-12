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
use Wanphp\Libray\Slim\Setting;

class ClearCacheAction extends Action
{
  private ClientInterface $redis;
  private Setting $setting;

  public function __construct(LoggerInterface $logger, ClientInterface $redis, Setting $setting)
  {
    $this->redis = $redis;
    $this->setting = $setting;
    parent::__construct($logger);
  }

  protected function action(): Response
  {
    $this->redis->select($this->setting->get('redis')['parameters']['database']);
    $this->redis->flushdb();
    return $this->respondWithData(['msg' => 'OK!']);
  }
}
