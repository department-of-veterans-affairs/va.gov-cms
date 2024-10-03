<?php

declare(strict_types=1);

namespace Drupal\va_gov_backend\Logger\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Processor to map levels to Datadog status.
 */
class DatadogLevelProcessor implements ProcessorInterface {

  /**
   * Processor function.
   *
   * @param \Monolog\LogRecord $record
   *   The record.
   *
   * @return \Monolog\LogRecord
   *   The processed record.
   */
  public function __invoke(LogRecord $record): LogRecord {
    // @todo Check if we really need this.
    return new LogRecord(
      $record->datetime,
      $record->channel,
      match($record->level) {
        Level::Debug, Level::Info, Level::Notice => Level::Info,
        Level::Warning => Level::Warning,
        Level::Error, Level::Alert, Level::Critical, Level::Emergency => Level::Error,
      },
      $record->message,
      $record->context,
      $record->extra,
      $record->formatted
    );
  }

}
