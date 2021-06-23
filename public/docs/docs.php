<?php
if (isset($_GET['path'])) {
  if ($_GET['path'] == '/docs.ymal') $yaml = file_get_contents(realpath('./') . $_GET['path']);
  else $yaml = file_get_contents(realpath('../../wanphp') . $_GET['path']);
  echo str_replace('localhost', $_SERVER['HTTP_HOST'], $yaml);
} else {
  require __DIR__ . '/../../vendor/autoload.php';
  $openapi = \OpenApi\scan(realpath('../../') . '/src');
  file_put_contents('docs.ymal', $openapi->toYaml());
}
