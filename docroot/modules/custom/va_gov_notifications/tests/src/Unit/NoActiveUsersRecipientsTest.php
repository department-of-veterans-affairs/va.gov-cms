<?php

declare(strict_types=1);

namespace Drupal\Tests\va_gov_notifications\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_notifications\Service\NoActiveUsersRecipients;

/**
 * Tests recipient merge and dedupe behavior.
 *
 * @group va_gov_notifications
 */
final class NoActiveUsersRecipientsTest extends UnitTestCase {

  /**
   * Tests ad hoc + role-derived recipient merge and section dedupe.
   */
  public function testMergesAndDedupesRecipients(): void {
    $service = $this->buildServiceWithFixtures(
      sections: [
        ['section_id' => 101, 'section_name' => 'Section Alpha'],
        ['section_id' => 102, 'section_name' => 'Section Beta'],
      ],
      roleDerivedRecipients: [
        'lead@va.gov' => [
          'recipient_email' => 'lead@va.gov',
          'recipient_uid' => 50,
          'recipient_name' => 'Lead User',
          'recipient_sources' => ['role_derived'],
          'sections' => [
            ['section_id' => 101, 'section_name' => 'Section Alpha'],
            ['section_id' => 101, 'section_name' => 'Section Alpha'],
          ],
        ],
        'second@va.gov' => [
          'recipient_email' => 'second@va.gov',
          'recipient_uid' => 51,
          'recipient_name' => 'Second User',
          'recipient_sources' => ['role_derived'],
          'sections' => [
            ['section_id' => 102, 'section_name' => 'Section Beta'],
          ],
        ],
      ],
      adHocRecipients: [
        ['recipient_email' => 'LEAD@VA.GOV', 'recipient_name' => 'Ad Hoc Lead'],
        ['recipient_email' => 'observer@va.gov', 'recipient_name' => 'Observer'],
      ],
    );

    $result = $service->getRecipientsForSectionsWithoutActiveUsers();

    $this->assertArrayHasKey('lead@va.gov', $result);
    $this->assertArrayHasKey('second@va.gov', $result);
    $this->assertArrayHasKey('observer@va.gov', $result);

    $lead = $result['lead@va.gov'];
    sort($lead['recipient_sources']);
    $this->assertSame(['ad_hoc', 'role_derived'], $lead['recipient_sources']);
    $this->assertCount(2, $lead['sections']);

    $observer = $result['observer@va.gov'];
    $this->assertSame(['ad_hoc'], $observer['recipient_sources']);
    $this->assertCount(2, $observer['sections']);

    $second = $result['second@va.gov'];
    $this->assertSame(['role_derived'], $second['recipient_sources']);
    $this->assertCount(1, $second['sections']);
  }

  /**
   * Tests that no sections means no recipients.
   */
  public function testNoSectionsReturnsNoRecipients(): void {
    $service = $this->buildServiceWithFixtures([], [], []);
    $this->assertSame([], $service->getRecipientsForSectionsWithoutActiveUsers());
  }

  /**
   * Builds a service with deterministic fixture data.
   *
   * @param array<int, array{section_id:int, section_name:string}> $sections
   *   Section fixtures.
   * @param array<string, array<string, mixed>> $roleDerivedRecipients
   *   Role-derived recipient fixtures keyed by normalized email.
   * @param array<int, array{recipient_email:string, recipient_name:string}> $adHocRecipients
   *   Ad hoc recipient fixtures.
   */
  private function buildServiceWithFixtures(array $sections, array $roleDerivedRecipients, array $adHocRecipients): NoActiveUsersRecipients {
    $database = $this->createMock(Connection::class);
    $entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $configFactory = $this->createMock(ConfigFactoryInterface::class);

    return new class($database, $entityTypeManager, $configFactory, $sections, $roleDerivedRecipients, $adHocRecipients) extends NoActiveUsersRecipients {

      /**
       * @param array<int, array{section_id:int, section_name:string}> $sections
       * @param array<string, array<string, mixed>> $roleDerivedRecipients
       * @param array<int, array{recipient_email:string, recipient_name:string}> $adHocRecipients
       */
      public function __construct(Connection $database, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, private array $sections, private array $roleDerivedRecipients, private array $adHocRecipients) {
        parent::__construct($database, $entity_type_manager, $config_factory);
      }

      /**
       * {@inheritdoc}
       */
      protected function getSectionsWithoutActiveUsers(): array {
        return $this->sections;
      }

      /**
       * {@inheritdoc}
       */
      protected function getRoleDerivedRecipients(array $sections_by_id): array {
        return $this->roleDerivedRecipients;
      }

      /**
       * {@inheritdoc}
       */
      protected function getAdHocRecipients(): array {
        return $this->adHocRecipients;
      }

    };
  }

}
