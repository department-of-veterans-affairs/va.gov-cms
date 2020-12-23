<?php

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Drush\Queue\Queue8;
use Symfony\Component\HttpFoundation\Request;

$doc_root = __DIR__ . '/../../docroot';
$autoloader = require $doc_root . '/autoload.php';
require_once $doc_root . '/core/includes/bootstrap.inc';
$request = Request::createFromGlobals();
Settings::initialize($doc_root, DrupalKernel::findSitePath($request), $autoloader);

$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod')->boot();

// @TODO change when we upgrade to drush10
$queue = new Queue8();

while (1 === 1) {
  $queue->run('va_gov_web_build');
  sleep(60);
}

