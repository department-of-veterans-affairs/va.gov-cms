<?php

/**
 * @file
 * Post deploy file for VA Gov Lovell.
 */

/**
 * Duplicates dual-published Lovell program pages.
 */
function va_gov_lovell_deploy_duplicate_lovell_program_pages(&$sandbox) {
  $script = \Drupal::classResolver('\Drupal\va_gov_batch\cbo_scripts\DuplicateLovellProgramPages');
  return $script->run($sandbox, 'deploy');
}
