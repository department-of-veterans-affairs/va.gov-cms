<?php

namespace Drupal\va_gov_backend\Logger\Processor;

use Drupal\va_gov_backend\Service\DatadogContextProviderInterface;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Parse and replace message placeholders for Datadog APM.
 */
class DatadogApmProcessor implements ProcessorInterface {

  /**
   * Environment Discovery Service.
   *
   * @var \Drupal\va_gov_backend\Service\DatadogContextProviderInterface
   */
  protected $contextProvider;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_backend\Service\DatadogContextProviderInterface $contextProvider
   *   An OOP-friendly method of retrieving the current context.
   */
  public function __construct(DatadogContextProviderInterface $contextProvider) {
    $this->contextProvider = $contextProvider;
  }

  /**
   * Retrieve the current context.
   *
   * @return array
   *   The current context.
   */
  public function getCurrentContext(): array {
    return $this->contextProvider->getCurrentContext();
  }

  /**
   * Alter the record message.
   *
   * @param string $message
   *   The record message.
   * @param array $context
   *   The current trace context.
   *
   * @return string
   *   An altered copy of the record message.
   */
  public function getAlteredMessage(string $message, array $context): string {
    return sprintf('%s [dd.trace_id=%d dd.span_id=%d]', $message, $context['trace_id'], $context['span_id']);
  }

  /**
   * Alter the log record.
   *
   * @param array|\Monolog\LogRecord $record
   *   The log message, as a record.
   *
   * @return array
   *   The altered record.
   */
  public function getAlteredRecord(array|LogRecord $record): array {
    $context = $this->getCurrentContext();
    $record['dd'] = [
      'trace_id' => $context['trace_id'],
      'span_id' => $context['span_id'],
    ];
    $record['message'] = $this->getAlteredMessage($record['message'], $context);
    return $record;
  }

  /**
   * Has this record already been processed?
   *
   * @param array|\Monolog\LogRecord $record
   *   The log message, as a record.
   *
   * @return bool
   *   TRUE if the record has already been processed, otherwise FALSE.
   */
  public function hasAlteredRecord(array|LogRecord $record): bool {
    return strpos(@$record['message'], '[dd.trace_id=') !== FALSE && isset($record['dd']);
  }

  /**
   * Should we alter this record?
   *
   * @param array|\Monolog\LogRecord $record
   *   The log message, as a record.
   *
   * @return bool
   *   Whether or not this record should be altered.
   */
  public function shouldAlterRecord(array|LogRecord $record): bool {
    return !$this->hasAlteredRecord($record);
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke(array|LogRecord $record): array {
    if (!$this->shouldAlterRecord($record)) {
      return $record;
    }
    return $this->getAlteredRecord($record);
  }

}
