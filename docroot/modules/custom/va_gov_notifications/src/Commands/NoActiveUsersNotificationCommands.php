<?php

namespace Drupal\va_gov_notifications\Commands;

use Drupal\va_gov_notifications\Service\NoActiveUsersNotificationService;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for missing active-user section reporting.
 */
class NoActiveUsersNotificationCommands extends DrushCommands {

  /**
   * Recipient resolver service.
   *
   * @var \Drupal\va_gov_notifications\Service\NoActiveUsersNotificationService
   */
  protected NoActiveUsersNotificationService $noActiveUsersNotificationService;

  /**
   * Constructor.
   */
  public function __construct(NoActiveUsersNotificationService $no_active_users_notification_service) {
    $this->noActiveUsersNotificationService = $no_active_users_notification_service;
  }

  /**
   * Reports recipients and section counts for no-active-users notifications.
   *
   * @command va-gov-notifications:no-active-users:report
   * @aliases va-gov-notifications-no-active-users-report
   * @option dry-run Output recipient mapping without queueing or sending.
   */
  public function report(array $options = ['dry-run' => FALSE]): void {
    $recipients = $this->noActiveUsersNotificationService->getRecipientsForSectionsWithoutActiveUsers();

    if (empty($recipients)) {
      $this->io()->warning('No recipients found for sections without active users.');
      return;
    }

    $rows = [];
    foreach ($recipients as $recipient) {
      $rows[] = [
        $recipient['recipient_email'],
        implode(', ', $recipient['recipient_sources']),
        count($recipient['sections']),
      ];
    }

    $this->io()->table(['Recipient email', 'Sources', 'Section count'], $rows);

    $total_sections = 0;
    foreach ($recipients as $recipient) {
      $total_sections += count($recipient['sections']);
    }

    $this->io()->success(sprintf(
      'Found %d recipients covering %d recipient-section mappings.',
      count($recipients),
      $total_sections
    ));

    if (!empty($options['dry-run'])) {
      $this->io()->text('Dry-run enabled: no queue jobs or email were sent.');
    }
  }

}
