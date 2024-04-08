<?php
declare(strict_types=1);

namespace App\Application\Actions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

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

  protected function resolveArg(string $name, $default = null): mixed
  {
    return $this->args[$name] ?? $default;
  }

  /**
   * @param string $template 模板路径
   * @param array $data
   * @return Response
   * @throws Exception
   */
  protected function respondView(string $template, array $data = []): Response
  {
    $tplVars = $this->request->getAttribute('tplVars') ?? [];
    if (is_array($tplVars)) $data = array_merge($data, $tplVars);
    if (isset($tplVars['basePath']) && is_file(ROOT_PATH . '/var' . $tplVars['basePath'] . '/' . $template)) $template = str_replace('/', '@', $tplVars['basePath']) . '/' . $template;
    return Twig::fromRequest($this->request)->render($this->response, $template, $data);
  }

  protected function httpHost(): string
  {
    return $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost();
  }

  protected function isAjax(): bool
  {
    return $this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest";
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

  protected function getIP(): string
  {
    $serverParams = $this->request->getServerParams();
    $ipAddress = '';

    // 检查是否存在代理服务器IP
    if (!empty($serverParams['HTTP_CLIENT_IP'])) {
      $ipAddress = $serverParams['HTTP_CLIENT_IP'];
    } elseif (!empty($serverParams['HTTP_X_FORWARDED_FOR'])) {
      // 多个代理服务器的情况下，获取最后一个IP地址
      $ipList = explode(',', $serverParams['HTTP_X_FORWARDED_FOR']);
      $ipAddress = trim(end($ipList));
    } elseif (!empty($serverParams['HTTP_X_FORWARDED'])) {
      $ipAddress = $serverParams['HTTP_X_FORWARDED'];
    } elseif (!empty($serverParams['HTTP_X_CLUSTER_CLIENT_IP'])) {
      $ipAddress = $serverParams['HTTP_X_CLUSTER_CLIENT_IP'];
    } elseif (!empty($serverParams['HTTP_FORWARDED_FOR'])) {
      $ipAddress = $serverParams['HTTP_FORWARDED_FOR'];
    } elseif (!empty($serverParams['HTTP_FORWARDED'])) {
      $ipAddress = $serverParams['HTTP_FORWARDED'];
    } elseif (!empty($serverParams['REMOTE_ADDR'])) {
      // 如果以上都不存在，使用REMOTE_ADDR
      $ipAddress = $serverParams['REMOTE_ADDR'];
    }

    return $ipAddress;
  }

  protected function getLoginUserId(): int
  {
    return $_SESSION['user_id'] ?? 0;
  }

  protected function getLoginUserRoleId(): int
  {
    return $_SESSION['role_id'] ?? 0;
  }

  protected function getLoginUserGroupId(): int
  {
    return $_SESSION['groupId'] ?? 0;
  }

  protected function getLoginId(): int
  {
    return $_SESSION['login_id'] ?? 0;
  }

  protected function thumb(string $image, string $size): string
  {
    $info = pathinfo($image);
    return str_replace('/image/', '/image/thumb/', $info['dirname']) . "/{$info['filename']}/{$size}.{$info['extension']}";
  }
}
