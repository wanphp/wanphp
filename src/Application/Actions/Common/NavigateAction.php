<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/28
 * Time: 9:25
 */

namespace App\Application\Actions\Common;


use App\Application\Actions\Action;
use App\Domain\Common\NavigateInterface;
use App\Repositories\Mysql\Router\PersistenceRepository;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class NavigateAction extends Action
{
  private NavigateInterface $navigate;
  private PersistenceRepository $persistence;

  public function __construct(LoggerInterface $logger, NavigateInterface $navigate, PersistenceRepository $persistence)
  {
    parent::__construct($logger);
    $this->navigate = $navigate;
    $this->persistence = $persistence;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'POST':
        $data = $this->request->getParsedBody();
        $id = $this->navigate->insert($data);
        return $this->respondWithData(['id' => $id], 201);
      case 'PUT':
        $data = $this->request->getParsedBody();
        $num = $this->navigate->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['upNum' => $num], 201);
      case 'DELETE':
        $delNum = $this->navigate->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['delNum' => $delNum]);
      default:
        return $this->respondWithData(array_merge($this->persistence->getSidebar()));
    }
  }
}
