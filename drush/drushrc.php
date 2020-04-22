<?php

/**
 * If there is a local drushrc file, then include it.
 */

$local_drushrc = __DIR__ . "/drushrc.local.php";

if (file_exists($local_drushrc)) {
  include $local_drushrc;
}
