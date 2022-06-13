<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/14
 * Time: 16:03
 */

namespace App\Application\Actions\Clients;


use App\Application\Actions\Action;
use App\Repositories\Mysql\Author2\ClientRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Class ListAction
 * * @title 客户端管理
 * @route /admin/clients
 * @package App\Application\Actions\Clients
 */
class ListAction extends Action
{
  private ClientRepository $clientRepository;

  public function __construct(LoggerInterface $logger, ClientRepository $clientRepository)
  {
    parent::__construct($logger);
    $this->clientRepository = $clientRepository;
  }

  protected function action(): Response
  {
    if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
      return $this->respondWithData(['data' => $this->clientRepository->select()]);
    } else {
      $data = [
        'title' => '客户端管理'
      ];

      return $this->respondView('admin/clients/list.html', $data);
    }
  }

}
