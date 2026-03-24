<?php

declare(strict_types=1);

namespace Drupal\Tests\va_gov_notifications\Unit;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_notifications\Service\NoActiveUsersNotificationService;

/**
 * Tests recipient product matching and dedupe behavior.
 *
 * @group va_gov_notifications
 */
final class NoActiveUsersNotificationServiceTest extends UnitTestCase {

  /**
   * Tests ad hoc + role-derived recipient merge and section dedupe.
   */
  public function testFiltersRecipientsByProductsAndDedupesSections(): void {
    $service = $this->buildServiceWithFixtures(
      sections: [
        ['section_id' => 101, 'section_name' => 'Section Alpha', 'product_ids' => ['284']],
        ['section_id' => 102, 'section_name' => 'Section Beta', 'product_ids' => ['289']],
      ],
      productOwnerContacts: [
        // Duplicate recipient with mixed case and duplicate product IDs.
        ['recipient_email' => 'LEAD@VA.GOV', 'recipient_name' => 'Ad Hoc Lead', 'product_ids' => ['284', '284']],
        ['recipient_email' => 'lead@va.gov', 'recipient_name' => 'Ad Hoc Lead 2', 'product_ids' => ['284']],
        ['recipient_email' => 'observer@va.gov', 'recipient_name' => 'Observer', 'product_ids' => []],
        ['recipient_email' => 'vet@va.gov', 'recipient_name' => 'Vet Recipient', 'product_ids' => ['289']],
        // No matching sections for this product.
        ['recipient_email' => 'none@va.gov', 'recipient_name' => 'No Match', 'product_ids' => ['999']],
      ],
    );

    $result = $service->getRecipientsForSectionsWithoutActiveUsers();

    $this->assertArrayHasKey('lead@va.gov', $result);
    $this->assertArrayHasKey('observer@va.gov', $result);
    $this->assertArrayHasKey('vet@va.gov', $result);
    $this->assertArrayNotHasKey('none@va.gov', $result);

    $lead = $result['lead@va.gov'];
    $this->assertSame(['product_owner_contact'], $lead['recipient_sources']);
    $this->assertCount(1, $lead['sections']);
    $this->assertSame(101, $lead['sections'][0]['section_id']);

    $observer = $result['observer@va.gov'];
    $this->assertSame(['product_owner_contact'], $observer['recipient_sources']);
    $this->assertCount(2, $observer['sections']);

    $vet = $result['vet@va.gov'];
    $this->assertSame(['product_owner_contact'], $vet['recipient_sources']);
    $this->assertCount(1, $vet['sections']);
    $this->assertSame(102, $vet['sections'][0]['section_id']);
  }

  /**
   * Tests that no sections means no recipients.
   */
  public function testNoSectionsReturnsNoRecipients(): void {
    $service = $this->buildServiceWithFixtures([], []);
    $this->assertSame([], $service->getRecipientsForSectionsWithoutActiveUsers());
  }

  /**
   * Builds a service with deterministic fixture data.
   *
   * @param array<int, array{section_id:int, section_name:string, product_ids:string[]}> $sections
   *   Section fixtures.
   * @param array<int, array{recipient_email:string, recipient_name:string, product_ids:string[]}> $productOwnerContacts
   *   Product owner contact fixtures.
   */
  private function buildServiceWithFixtures(array $sections, array $productOwnerContacts): NoActiveUsersNotificationService {
    $database = $this->createMock(Connection::class);
    $entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);

    return new class($database, $entityTypeManager, $sections, $productOwnerContacts) extends NoActiveUsersNotificationService {

      /**
       * @param \Drupal\Core\Database\Connection $database
       *   Database connection mock.
       * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
       *   Entity type manager mock.
       * @param array<int, array{section_id:int, section_name:string, product_ids:string[]}> $sections
       *   Section fixtures.
       * @param array<int, array{recipient_email:string, recipient_name:string, product_ids:string[]}> $productOwnerContacts
       *   Product owner contact fixtures.
       */
      public function __construct(Connection $database, EntityTypeManagerInterface $entity_type_manager, private array $sections, private array $productOwnerContacts) {
        parent::__construct($database, $entity_type_manager);
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
      protected function getProductOwnerContacts(): array {
        return $this->productOwnerContacts;
      }

    };
  }

}
