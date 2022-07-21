<?php
// phpcs:ignoreFile
/**
 * @file
 * Personal settings file.
 *
 * Use this file to customize any Drupal settings to your liking for local
 * development.
 *
 * To activate this file, copy this file to settings.personal.php and uncomment
 * lines you wish to enable, or add your own. The copy will be .gitignored.
 */

// Uncomment this line to temporarily enable sending metrics to datadog on cron.
// $settings['va_gov_force_sending_metrics'] = true;


/**
 * Enable local development services.
 *
 * Uncomment these lines to enable development-oriented settings like disabling
 * backend cache or enabling twig debug.
 */
// Development settings provided by Drupal core.
// $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
// Custom development settings.
// $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/vagov-dev.services.yml';
