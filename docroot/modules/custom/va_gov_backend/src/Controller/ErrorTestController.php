<?php

namespace Drupal\va_gov_backend\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller class for testing errors.
 */
class ErrorTestController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function errorTestPage($errorType) : array {

    switch ($errorType) {

      case 'uncaught':
        throw new \Exception('Uncaught Exception');

      case 'error':
        trigger_error('Trigger an E_ERROR manually', E_USER_ERROR);
        break;

      case 'caught-nothing':
        try {
          throw new \Exception('Caught but not logged');
        }
        catch (\Exception $e) {
          // Do nothing.
        }
        break;

      case 'caught-watchdog-exception':
        try {
          throw new \Exception('Caught and sent to watchdog exception');
        }
        catch (\Exception $e) {
          watchdog_exception('va_sentry_test', $e);
        }
        break;

      case 'caught-and-logged':
        try {
          throw new \Exception('Caught and sent to logger');
        }
        catch (\Exception $e) {
          $this->getLogger('va_sentry_test')->error('Caught and sent to logger class');
        }

    }

    return ['#markup' => 'Look in Sentry'];
  }

}
