<?php

declare(strict_types=1);

namespace Drupal\va_gov_backend\Logger;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * @todo Add a description for the logger.
 */
final class VaGovTestLogging implements LoggerInterface {

  use RfcLoggerTrait;

  /**
   * Constructs a VaGovTestLogging object.
   */
  public function __construct(
    private readonly LogMessageParserInterface $parser,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function log($level, string|\Stringable $message, array $context = []): void {
    // Convert PSR3-style messages to \Drupal\Component\Render\FormattableMarkup
    // style, so they can be translated too.
    $placeholders = $this->parser->parseMessagePlaceholders($message, $context);
    // @see \Drupal\Core\Logger\LoggerChannel::log() for all available contexts.
    $rendered_message = strtr($message, $placeholders);
    // @todo Log the rendered message here.
  }

}
