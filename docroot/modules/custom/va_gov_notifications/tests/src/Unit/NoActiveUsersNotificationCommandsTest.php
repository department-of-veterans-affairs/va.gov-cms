<?php

declare(strict_types=1);

namespace Drupal\Tests\va_gov_notifications\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_notifications\Commands\NoActiveUsersNotificationCommands;
use Drupal\va_gov_notifications\Service\NoActiveUsersNotificationService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Tests command behavior for no-active-users reporting.
 *
 * @group va_gov_notifications
 */
final class NoActiveUsersNotificationCommandsTest extends UnitTestCase {

  /**
   * Tests warning output when no recipients are available.
   */
  public function testReportWithNoRecipients(): void {
    $service = $this->createMock(NoActiveUsersNotificationService::class);
    $service->method('getRecipientsForSectionsWithoutActiveUsers')->willReturn([]);

    [$command, $output] = $this->buildCommandWithOutput($service);
    $command->report(['dry-run' => TRUE]);

    $text = $output->fetch();
    $this->assertStringContainsString('No recipients found for sections without active users.', $text);
  }

  /**
   * Tests table and dry-run output with recipient data.
   */
  public function testReportWithRecipientsAndDryRun(): void {
    $service = $this->createMock(NoActiveUsersNotificationService::class);
    $service->method('getRecipientsForSectionsWithoutActiveUsers')->willReturn([
      'lead@va.gov' => [
        'recipient_email' => 'lead@va.gov',
        'recipient_sources' => ['role_derived', 'product_owner_contact'],
        'sections' => [
          ['section_id' => 101, 'section_name' => 'Section Alpha'],
          ['section_id' => 102, 'section_name' => 'Section Beta'],
        ],
      ],
    ]);

    [$command, $output] = $this->buildCommandWithOutput($service);
    $command->report(['dry-run' => TRUE]);

    $text = $output->fetch();
    $this->assertStringContainsString('Recipient email', $text);
    $this->assertStringContainsString('lead@va.gov', $text);
    $this->assertStringContainsString('Found 1 recipients covering 2 recipient-section mappings.', $text);
    $this->assertStringContainsString('Dry-run enabled: no queue jobs or email were sent.', $text);
  }

  /**
   * Builds a command with buffered console output.
   *
   * @return array{0: \Drupal\va_gov_notifications\Commands\NoActiveUsersNotificationCommands, 1: \Symfony\Component\Console\Output\BufferedOutput}
   *   Command and output instances.
   */
  private function buildCommandWithOutput(NoActiveUsersNotificationService $service): array {
    $input = new ArrayInput([]);
    $output = new BufferedOutput();
    $io = new SymfonyStyle($input, $output);

    $command = new class($service, $io) extends NoActiveUsersNotificationCommands {

      /**
       * Constructor.
       */
      public function __construct(NoActiveUsersNotificationService $no_active_users_notification_service, private SymfonyStyle $testIo) {
        parent::__construct($no_active_users_notification_service);
      }

      /**
       * {@inheritdoc}
       */
      protected function io(): SymfonyStyle {
        return $this->testIo;
      }

    };

    return [$command, $output];
  }

}
