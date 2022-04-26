<?php

namespace Drupal\va_gov_backend\Logger\Processor;

use Monolog\Processor\ProcessorInterface;

/**
 * Parse and replace message placeholders for Datadog APM.
 */
class DatadogApmProcessor implements ProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function __invoke(array $record): array {
    $context = \DDTrace\current_context();
    $traceId = $context['trace_id'];
    $spanId = $context['span_id'];
    $record['dd'] = [
      'trace_id' => $traceId,
      'span_id' => $spanId,
    ];
    $record['message'] .= sprintf(' [dd.trace_id=%d dd.span_id=%d]', $traceId, $spanId);

    return $record;
  }

}
