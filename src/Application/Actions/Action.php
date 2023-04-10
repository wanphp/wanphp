<?php
declare(strict_types=1);

namespace App\Application\Actions;

use Psr\Log\LoggerInterface;

abstract class Action extends \Wanphp\Libray\Slim\Action
{
  /**
   * @var LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * @param LoggerInterface $logger
   */
  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  //添加使用
  protected function isPost(): bool
  {
    return $this->request->getMethod() == 'POST';
  }

  protected function isGet(): bool
  {
    return $this->request->getMethod() == 'GET';
  }

  //更新所有
  protected function isPut(): bool
  {
    return $this->request->getMethod() == 'PUT';
  }

  //更新属性
  protected function isPatch(): bool
  {
    return $this->request->getMethod() == 'PATCH';
  }

  protected function isDelete(): bool
  {
    return $this->request->getMethod() == 'DELETE';
  }

  protected function thumb(string $image, string $size): string
  {
    $info = pathinfo($image);
    return str_replace('/image/', '/image/thumb/', $info['dirname']) . "/{$info['filename']}/{$size}.{$info['extension']}";
  }
}
