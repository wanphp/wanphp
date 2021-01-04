<?php
require __DIR__ . '/../../vendor/autoload.php';
$openapi = \OpenApi\scan('/var/www/src');
//header('Content-Type: application/x-yaml');
header('Access-Control-Allow-Origin: https://slim.wanphp.com');
echo $openapi->toYaml();
