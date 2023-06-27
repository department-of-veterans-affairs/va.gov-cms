<?php

namespace Drupal\va_gov_content_release\Reporter;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * The content release reporter service.
 *
 * This service is used to report events in the content release process.
 *
 * In its current form, it simply logs messages and displays them to the user.
 */
class Reporter implements ReporterInterface {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory service.
   */
  public function __construct(MessengerInterface $messenger, LoggerChannelFactoryInterface $loggerFactory) {
    $this->messenger = $messenger;
    $this->logger = $loggerFactory->get('va_gov_content_release');
  }

  /**
   * {@inheritDoc}
   */
  public function reportInfo(string $message) : void {
    $this->messenger->addStatus($message);
    $this->logger->info($message);
  }

  /**
   * {@inheritDoc}
   */
  public function reportError(string $message, \Throwable $exception = NULL) : void {
    $this->messenger->addError($message);
    $this->logger->error($message);
    if ($exception) {
      // When we are on Drupal 10.0.0, we can use the following:
      // Error::logException($this->logger, $exception);
      // Until then, we have to do this:
      watchdog_exception('va_gov_content_release', $exception);
    }
  }

}
