<?php

namespace Drupal\va_gov_backend\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

class ErrorTestController extends ControllerBase {

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get('va_sentry_test');
  }

  public function errorTestPage($errorType) : array {

    switch ($errorType) {
      case 'uncaught':
        throw new \Exception('Uncaught Exception');
        break;

      case 'error':
        trigger_error('Trigger an E_ERROR manually', E_USER_ERROR);
        break;

      case 'caught-nothing':
        try {
          throw new \Exception('Caught but not logged');
        } catch (\Exception $e) {
          // do nothing
        }
        break;

      case 'caught-watchdog-exception':
        try {
          throw new \Exception('Caught and sent to watchdog exception');
        } catch (\Exception $e) {
          watchdog_exception('va_sentry_test', $e);
        }
        break;

      case 'caught-and-logged':
        try {
          throw new \Exception('Caught and sent to logger');
        } catch (\Exception $e) {
          $this->logger->error('Caught and sent to logger class');
        }
    }

    return ['#markup' => 'Look in Sentry'];
  }

}
