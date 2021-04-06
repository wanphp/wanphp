<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/21
 * Time: 10:29
 */

namespace App\Application\Api\Auth;


use Predis\Client;
use Wanphp\Libray\Mysql\Database;
use Wanphp\Libray\Weixin\MiniProgram;
use App\Application\Api\Api;
use App\Domain\Weixin\MiniProgramInterface;
use App\Repositories\Mysql\Author2\AccessTokenRepository;
use App\Repositories\Mysql\Author2\AuthCodeRepository;
use App\Repositories\Mysql\Author2\ClientRepository;
use App\Repositories\Mysql\Author2\MiniProgramUserRepository;
use App\Repositories\Mysql\Author2\RefreshTokenRepository;
use App\Repositories\Mysql\Author2\ScopeRepository;
use App\Repositories\Mysql\Author2\UserRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Psr\Container\ContainerInterface;
use Slim\Exception\HttpNotFoundException;

abstract class Author2Api extends Api
{
  protected $server;
  protected $database;
  protected $redis;

  /**
   * Author2Api constructor.
   * @param Database $database
   * @param Client $redis
   * @throws \Exception
   */
  public function __construct(ContainerInterface $container)
  {
    $this->database = $container->get(Database::class);;
    $this->redis = new Client($container->get('redis'));
    $settings = $container->get('settings');

    // 初始化存储库
    $clientRepository = new ClientRepository($this->database);
    $scopeRepository = new ScopeRepository();
    $this->redis->select($settings['authRedis']);//选择库
    $accessTokenRepository = new AccessTokenRepository($this->redis);

    // 私钥与加密密钥
    $privateKey = new CryptKey($settings['privateKey'], $settings['privateKeyPass'] ?: null); // 如果私钥文件有密码
    //$encryptionKey = 'e9habxOA6IERAr3EXSSm+a231VX+lI5zVMiY4c7RF6s='; // 加密密钥字符串
    $encryptionKey = \Defuse\Crypto\Key::loadFromAsciiSafeString($settings['encryptionKey']); //如果通过 generate-defuse-key 脚本生成的字符串，可使用此方法传入

    // 初始化 server
    $this->server = new AuthorizationServer(
      $clientRepository,
      $accessTokenRepository,
      $scopeRepository,
      $privateKey,
      $encryptionKey
    );
  }

  protected function implicit()
  {
    $this->server->enableGrantType(
      new ImplicitGrant(new \DateInterval('PT1H')),
      new \DateInterval('PT1H') // access tokens will expire after 1 hour
    );
  }

  /**
   * @throws HttpNotFoundException
   */
  protected function authorization_code()
  {
    // 授权码授权类型初始化
    $authCodeRepository = new AuthCodeRepository($this->redis);
    $refreshTokenRepository = new RefreshTokenRepository($this->redis);
    try {
      $grant = new AuthCodeGrant(
        $authCodeRepository,
        $refreshTokenRepository,
        new \DateInterval('PT10M') // 设置授权码过期时间为10分钟
      );
    } catch (\Exception $e) {
      throw new HttpNotFoundException($this->request, $e->getMessage());
    }

    $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // 设置刷新令牌过期时间1个月

    // 将授权码授权类型添加进 server
    $this->server->enableGrantType(
      $grant,
      new \DateInterval('PT1H') // 设置访问令牌过期时间1小时
    );
  }

  protected function client_credentials()
  {
    $this->server->enableGrantType(
      new ClientCredentialsGrant(),
      new \DateInterval('PT1H') // access tokens will expire after 1 hour
    );
  }

  protected function password()
  {
    $userRepository = new UserRepository($this->database);
    $refreshTokenRepository = new RefreshTokenRepository($this->redis);

    $grant = new PasswordGrant(
      $userRepository,
      $refreshTokenRepository
    );

    $grant->setRefreshTokenTTL(new \DateInterval('PT2H')); //两个小时过期 refresh tokens will expire after 1 month P1M

    $this->server->enableGrantType(
      $grant,
      new \DateInterval('PT1H') // access tokens will expire after 1 hour
    );
  }

  protected function miniProgram(MiniProgram $miniProgram, MiniProgramInterface $miniProgramUser)
  {
    $userRepository = new MiniProgramUserRepository($miniProgram, $miniProgramUser, $this->database);
    $refreshTokenRepository = new RefreshTokenRepository($this->redis);

    $grant = new PasswordGrant(
      $userRepository,
      $refreshTokenRepository
    );

    $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

    $this->server->enableGrantType(
      $grant,
      new \DateInterval('PT1H') // access tokens will expire after 1 hour
    );
  }

  protected function refresh_token()
  {
    $refreshTokenRepository = new RefreshTokenRepository($this->redis);
    $grant = new RefreshTokenGrant($refreshTokenRepository);
    $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // new refresh tokens will expire after 1 month

    $this->server->enableGrantType(
      $grant,
      new \DateInterval('PT1H') // new access tokens will expire after an hour
    );
  }
}
