<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$tugboat_root = getenv('DOCROOT');
$autoloader = $tugboat_root . '/autoload.php';

$request = Request::createFromGlobals();
DrupalKernel::createFromRequest($request, $autoloader, 'prod');

$i = 0;
while (1 == 1) {
  echo $i . PHP_EOL;
  error_log($i);
  $i++;
  sleep(2);
}

