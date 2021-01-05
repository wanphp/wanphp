<?php
require __DIR__ . '/../../vendor/autoload.php';
$openapi = \OpenApi\scan(realpath('../../') . '/src');
//header('Content-Type: application/x-yaml');
header('Access-Control-Allow-Origin: https://slim.wanphp.com');
echo $openapi->toYaml();
