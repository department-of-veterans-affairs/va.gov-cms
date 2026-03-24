<?php

namespace Drupal\va_gov_notifications\Commands;

use Drupal\va_gov_notifications\Service\NoActiveUsersRecipients;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for missing active-user section reporting.
 */
class NoActiveUsersRecipientsCommands extends DrushCommands {

  /**
   * Recipient resolver service.
   *
   * @var \Drupal\va_gov_notifications\Service\NoActiveUsersRecipients
   */
  protected NoActiveUsersRecipients $noActiveUsersRecipients;

  /**
   * Constructor.
   */
  public function __construct(NoActiveUsersRecipients $no_active_users_recipients) {
    $this->noActiveUsersRecipients = $no_active_users_recipients;
  }

  /**
   * Reports recipients and section counts for no-active-users notifications.
   *
   * @command va-gov-notifications:no-active-users:report
   * @aliases va-gov-notifications-no-active-users-report
   * @option dry-run Output recipient mapping without queueing or sending.
   */
  public function report(array $options = ['dry-run' => FALSE]): void {
    $recipients = $this->noActiveUsersRecipients->getRecipientsForSectionsWithoutActiveUsers();

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
