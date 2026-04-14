<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\va_gov_notifications\Entity\ProductOwnerContactInterface;

/**
 * Builds recipient lists for sections missing active users.
 */
class NoActiveUsersNotificationService {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entity_type_manager) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Gets recipients grouped with sections missing active users.
   *
   * @return array<string, array>
   *   Array keyed by normalized email.
   */
  public function getRecipientsForSectionsWithoutActiveUsers(): array {
    $sections = $this->getSectionsWithoutActiveUsers();
    if (empty($sections)) {
      return [];
    }

    $sections_by_id = [];
    foreach ($sections as $section) {
      $sections_by_id[(int) $section['section_id']] = [
        'section_id' => (int) $section['section_id'],
        'section_name' => (string) $section['section_name'],
        'product_ids' => array_values(array_unique(array_filter(array_map('strval', $section['product_ids'] ?? [])))),
      ];
    }

    $recipients = [];
    foreach ($this->getProductOwnerContacts() as $product_owner_contact) {
      $key = $this->normalizeRecipientKey($product_owner_contact['recipient_email']);
      $sections_for_recipient = $this->filterSectionsByProducts($sections_by_id, $product_owner_contact['product_ids']);
      if (empty($sections_for_recipient)) {
        continue;
      }

      if (!isset($recipients[$key])) {
        $recipients[$key] = [
          'recipient_email' => $product_owner_contact['recipient_email'],
          'recipient_uid' => NULL,
          'recipient_name' => $product_owner_contact['recipient_name'],
          'recipient_sources' => [],
          'sections' => [],
        ];
      }
      $recipients[$key]['recipient_sources'][] = 'product_owner_contact';
      $recipients[$key]['sections'] = array_merge($recipients[$key]['sections'], $sections_for_recipient);
    }

    foreach ($recipients as &$recipient) {
      $recipient['sections'] = $this->dedupeSections($recipient['sections']);
      $recipient['recipient_sources'] = array_values(array_unique($recipient['recipient_sources']));
    }

    return $recipients;
  }

  /**
   * Returns sections that currently have no active users.
   *
   * @return array<int, array{section_id:int, section_name:string, product_ids:string[]}>
   *   Section rows.
   */
  protected function getSectionsWithoutActiveUsers(): array {
    $query = $this->database->select('section_association', 'sa');
    $query->join('taxonomy_term_field_data', 'ttfd', 'ttfd.tid = sa.section_id');
    $query->leftJoin('section_association__user_id', 'sau', 'sau.entity_id = sa.id');
    $query->leftJoin('users_field_data', 'u', 'u.uid = sau.user_id_target_id');

    $query->addField('sa', 'section_id', 'section_id');
    $query->addField('ttfd', 'name', 'section_name');
    $query->condition('ttfd.vid', 'administration');
    $query->groupBy('sa.section_id');
    $query->groupBy('ttfd.name');

    // No active users in the section means sum of active status values is 0.
    $query->having('COALESCE(SUM(CASE WHEN u.status = 1 THEN 1 ELSE 0 END), 0) = 0');

    $query->orderBy('ttfd.name');
    $result = $query->execute()->fetchAllAssoc('section_id');
    $section_ids = array_map('intval', array_keys($result));

    $product_ids_by_section = [];
    if (!empty($section_ids)) {
      $product_query = $this->database->select('taxonomy_term__field_product', 'tfp');
      $product_query->fields('tfp', ['entity_id', 'field_product_target_id']);
      $product_query->condition('tfp.entity_id', $section_ids, 'IN');
      $product_query->condition('tfp.deleted', 0);
      foreach ($product_query->execute()->fetchAll() as $row) {
        $section_id = (int) $row->entity_id;
        $product_ids_by_section[$section_id][] = (string) $row->field_product_target_id;
      }
    }

    return array_values(array_map(static function ($row) use ($product_ids_by_section): array {
      $section_id = (int) $row->section_id;
      return [
        'section_id' => $section_id,
        'section_name' => (string) $row->section_name,
        'product_ids' => array_values(array_unique($product_ids_by_section[$section_id] ?? [])),
      ];
    }, $result));
  }

  /**
   * Gets enabled product owner contacts from config entities.
   *
   * @return array<int, array{recipient_email:string, recipient_name:string, product_ids:string[]}>
   *   Enabled product owner contacts.
   */
  protected function getProductOwnerContacts(): array {
    $storage = $this->entityTypeManager->getStorage('product_owner_contact');
    $entities = $storage->loadMultiple();
    $recipients = [];

    foreach ($entities as $entity) {
      if (!$entity instanceof ProductOwnerContactInterface || !$entity->isEnabled()) {
        continue;
      }

      $email = strtolower(trim($entity->getEmail()));
      if ($email === '') {
        continue;
      }

      $recipients[] = [
        'recipient_email' => $email,
        'recipient_name' => (string) $entity->label(),
        'product_ids' => $entity->getProducts(),
      ];
    }

    return $recipients;
  }

  /**
   * Filters sections by product IDs.
   *
   * @param array<int, array{section_id:int, section_name:string, product_ids:string[]}> $sections_by_id
   *   Indexed by section id.
   * @param string[] $recipient_product_ids
   *   Product IDs assigned to the recipient.
   *
   * @return array<int, array{section_id:int, section_name:string, product_ids:string[]}>
   *   Matching sections.
   */
  protected function filterSectionsByProducts(array $sections_by_id, array $recipient_product_ids): array {
    $recipient_product_ids = array_values(array_unique(array_filter(array_map('strval', $recipient_product_ids))));
    if (empty($recipient_product_ids)) {
      return array_values($sections_by_id);
    }

    $matches = [];
    foreach ($sections_by_id as $section) {
      if (array_intersect($recipient_product_ids, $section['product_ids'])) {
        $matches[] = $section;
      }
    }
    return $matches;
  }

  /**
   * Dedupe sections by section id.
   *
   * @param array<int, array{section_id:int, section_name:string, product_ids:string[]}> $sections
   *   Sections list.
   *
   * @return array<int, array{section_id:int, section_name:string, product_ids:string[]}>
   *   Dedupe sections list.
   */
  protected function dedupeSections(array $sections): array {
    $unique = [];
    foreach ($sections as $section) {
      $unique[$section['section_id']] = $section;
    }
    return array_values($unique);
  }

  /**
   * Creates a normalized recipient key.
   */
  protected function normalizeRecipientKey(string $email): string {
    return strtolower(trim($email));
  }

}
