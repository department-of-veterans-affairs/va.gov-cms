<?php

/**
 * @file
 * If there is a local drushrc file, then include it.
 */

$local_drushrc = __DIR__ . "/drushrc.local.php";
if (file_exists($local_drushrc)) {
  include $local_drushrc;
}

// Ensure env vars are set for drush commands.
require_once __DIR__ . '/../scripts/composer/EnvironmentHandler.php';
