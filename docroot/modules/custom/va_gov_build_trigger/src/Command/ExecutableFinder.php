<?php

namespace Drupal\va_gov_build_trigger\Command;

/**
 * Find the drush exectable.
 */
class ExecutableFinder {

  /**
   * Find the executable.
   *
   * @param string $command
   *   The name of the command.
   *
   * @return string
   *   An executable string, i.e. "drush @foo.bar" or "./vendor/bin/drupal".
   */
  public function findExecutable(string $command) : string {
    $args = [];
    foreach ($_SERVER['argv'] as $arg) {
      if ($arg === $command) {
        break;
      }
      if (strpos($arg, '--backend') !== 0) {
        $args[] = $arg;
      }
    }
    if (isset($_SERVER['PWD']) && !is_file($args[0]) && is_file($_SERVER['PWD'] . '/' . $args[0])) {
      $args[0] = $_SERVER['PWD'] . '/' . $args[0];
    }
    return implode(' ', $args);
  }

}
