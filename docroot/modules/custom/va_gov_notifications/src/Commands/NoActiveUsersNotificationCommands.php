<?php

namespace Drupal\va_gov_notifications\Commands;

use Drupal\eca_base\BaseEvents;
use Drupal\eca_base\Event\CustomEvent;
use Drupal\va_gov_notifications\Service\NoActiveUsersNotificationService;
use Drush\Commands\DrushCommands;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * Constructor.
   */
  public function __construct(NoActiveUsersNotificationService $no_active_users_notification_service, EventDispatcherInterface $event_dispatcher) {
    $this->noActiveUsersNotificationService = $no_active_users_notification_service;
    $this->eventDispatcher = $event_dispatcher;
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

  /**
   * Triggers the ECA custom event for no-active-users notifications.
   *
   * @command va-gov-notifications:no-active-users:trigger
   * @aliases va-gov-notifications-no-active-users-trigger
   * @option event-id ECA custom event ID to dispatch.
   */
  public function trigger(array $options = ['event-id' => 'product_owner_no_active_users_notification']): void {
    $event_id = trim((string) ($options['event-id'] ?? ''));
    if ($event_id === '') {
      $this->io()->error('An event ID is required.');
      return;
    }

    $recipients = $this->noActiveUsersNotificationService->getRecipientsForSectionsWithoutActiveUsers();
    $recipient_count = count($recipients);
    $section_count = 0;
    foreach ($recipients as $recipient) {
      $section_count += count($recipient['sections']);
    }

    $this->eventDispatcher->dispatch(new CustomEvent($event_id), BaseEvents::CUSTOM);

    $this->io()->success(sprintf(
      'Dispatched ECA custom event "%s". Current recipient map includes %d recipients across %d recipient-section mappings.',
      $event_id,
      $recipient_count,
      $section_count
    ));

    if ($recipient_count === 0) {
      $this->io()->warning('No recipients currently match the no-active-users query, so the workflow is expected to queue no jobs in this environment.');
    }
  }

}
