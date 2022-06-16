<?php

/**
 * @file
 * Set Drupal URL for local environments.
 */

$env_type = getenv('CMS_ENVIRONMENT_TYPE');
if (isset($env_type) && $env_type === 'local') {
  $options['uri'] = 'https://va-gov-cms.ddev.site';
}
