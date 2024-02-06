<?php
declare(strict_types=1);

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

define('ROOT_PATH', realpath(__DIR__ . '/../'));
require ROOT_PATH . '/vendor/autoload.php';
// 实例化 PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

//if (false) { // 生产中应设置为true
//  $containerBuilder->enableCompilation(ROOT_PATH . '/var/cache');
//}

// Set up settings
$settings = require ROOT_PATH . '/app/settings.php';
$settings($containerBuilder);

// 设置依赖
$dependencies = require ROOT_PATH . '/app/dependencies.php';
$dependencies($containerBuilder);

// 设置存储库
$repositories = require ROOT_PATH . '/app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$basePath = $container->get(\Wanphp\Libray\Slim\Setting::class)->get('basePath');
if ($basePath) $app->setBasePath($basePath);
$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require ROOT_PATH . '/app/middleware.php';
$middleware($app);

// Register routes
$routes = require ROOT_PATH . '/app/routes.php';
$routes($app);

/** @var bool $displayErrorDetails 显示错误详细信息 */
$displayErrorDetails = $container->get(\Wanphp\Libray\Slim\Setting::class)->get('displayErrorDetails');

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// 创建错误处理程序
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory, $container->get(\Psr\Log\LoggerInterface::class));

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// 添加错误中间件
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// 运行应用并发出响应
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
