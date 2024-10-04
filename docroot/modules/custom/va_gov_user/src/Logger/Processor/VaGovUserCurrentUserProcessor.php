<?php

namespace Drupal\va_gov_user\Logger\Processor;

use Drupal\Core\Session\AccountProxyInterface;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Processor that adds user information to the log records.
 */
class VaGovUserCurrentUserProcessor implements ProcessorInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $accountProxy;

  /**
   * Constructs a Default object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The current user.
   */
  public function __construct(AccountProxyInterface $account_proxy) {
    $this->accountProxy = $account_proxy;
  }

  /**
   * {@inheritdoc}
   */
  public function __invoke(LogRecord $record): LogRecord {
    $record->extra = \array_merge(
      $record->extra,
      [
        'uid' => $this->accountProxy->id(),
        'user' => $this->accountProxy->id(),
      ],
    );

    return $record;
  }

}
