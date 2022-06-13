<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/29
 * Time: 12:03
 */

namespace App\Application\Actions\Permission;


use App\Application\Actions\Action;
use App\Domain\Common\RouterInterface;
use Psr\Log\LoggerInterface;

abstract class Router extends Action
{
  /**
   * @var RouterInterface
   */
  protected RouterInterface $routerRepository;

  /**
   * Action constructor.
   * @param LoggerInterface $logger
   * @param RouterInterface $routerRepository
   */
  public function __construct(LoggerInterface $logger, RouterInterface $routerRepository)
  {
    parent::__construct($logger);
    $this->routerRepository = $routerRepository;
  }
}
