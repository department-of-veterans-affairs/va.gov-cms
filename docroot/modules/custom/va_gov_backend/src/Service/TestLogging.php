<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Adds multiple test log messages to verify ddog filtering.
 */
final class TestLogging implements TestLoggingInterface {

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a TestLogging object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   */
  public function __construct(
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function runTest(): void {
    $logger = $this->loggerFactory->get('va_gov_testing');
    $logger->info('This is a test info log message containing a VA email. test_harry.styles@va.gov');
    $logger->notice('This is a test notice log message containing a non-VA email. test_peter.parker@gmail.com');
    $logger->warning('This is a test warning log message.');
    $logger->error('This is a test error log message.');
    $logger->critical('This is a test critical log message.');
  }

}
