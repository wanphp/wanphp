<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/3
 * Time: 14:45
 */

namespace App\Application\Actions\Home;


use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class HomeAction extends Action
{

  public function __construct(LoggerInterface $logger)
  {
    parent::__construct($logger);
  }

  protected function action(): Response
  {
    $data = [
      'title' => '管理首页'
    ];

    return $this->respondView('admin/index.html', $data);
  }

}
