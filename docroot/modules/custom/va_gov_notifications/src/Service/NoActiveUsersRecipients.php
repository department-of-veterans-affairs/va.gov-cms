<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\va_gov_notifications\Entity\NoActiveUsersRecipientInterface;

/**
 * Builds recipient lists for sections missing active users.
 */
class NoActiveUsersRecipients {

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
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructor.
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
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
      ];
    }

    $recipients = $this->getRoleDerivedRecipients($sections_by_id);

    // Ad hoc recipients receive the full report, regardless of section mapping.
    foreach ($this->getAdHocRecipients() as $ad_hoc) {
      $key = $this->normalizeRecipientKey($ad_hoc['recipient_email']);
      if (!isset($recipients[$key])) {
        $recipients[$key] = [
          'recipient_email' => $ad_hoc['recipient_email'],
          'recipient_uid' => NULL,
          'recipient_name' => $ad_hoc['recipient_name'],
          'recipient_sources' => [],
          'sections' => [],
        ];
      }
      $recipients[$key]['recipient_sources'][] = 'ad_hoc';
      $recipients[$key]['sections'] = array_values($sections_by_id);
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
   * @return array<int, array{section_id:int, section_name:string}>
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

    return array_values(array_map(static function ($row): array {
      return [
        'section_id' => (int) $row->section_id,
        'section_name' => (string) $row->section_name,
      ];
    }, $result));
  }

  /**
   * Gets role-derived recipients by section assignment.
   *
   * @param array<int, array{section_id:int, section_name:string}> $sections_by_id
   *   Indexed by section_id.
   *
   * @return array<string, array>
   *   Array keyed by email.
   */
  protected function getRoleDerivedRecipients(array $sections_by_id): array {
    $roles = $this->getConfiguredRecipientRoles();
    if (empty($roles)) {
      return [];
    }

    $section_ids = array_keys($sections_by_id);
    if (empty($section_ids)) {
      return [];
    }

    $query = $this->database->select('section_association', 'sa');
    $query->join('section_association__user_id', 'sau', 'sau.entity_id = sa.id');
    $query->join('users_field_data', 'u', 'u.uid = sau.user_id_target_id');
    $query->join('user__roles', 'ur', 'ur.entity_id = u.uid');

    $query->addField('sa', 'section_id', 'section_id');
    $query->addField('u', 'uid', 'uid');
    $query->addField('u', 'mail', 'mail');
    $query->addField('u', 'name', 'name');

    $query->condition('sa.section_id', $section_ids, 'IN');
    $query->condition('u.status', 1);
    $query->condition('ur.roles_target_id', $roles, 'IN');
    $query->isNotNull('u.mail');
    $query->orderBy('u.mail');

    $rows = $query->execute()->fetchAll();
    $recipients = [];

    foreach ($rows as $row) {
      $email = strtolower(trim((string) $row->mail));
      if ($email === '') {
        continue;
      }
      $key = $this->normalizeRecipientKey($email);
      if (!isset($recipients[$key])) {
        $recipients[$key] = [
          'recipient_email' => $email,
          'recipient_uid' => (int) $row->uid,
          'recipient_name' => (string) $row->name,
          'recipient_sources' => ['role_derived'],
          'sections' => [],
        ];
      }

      $section_id = (int) $row->section_id;
      if (isset($sections_by_id[$section_id])) {
        $recipients[$key]['sections'][] = $sections_by_id[$section_id];
      }
    }

    return $recipients;
  }

  /**
   * Gets enabled ad hoc recipients from config entities.
   *
   * @return array<int, array{recipient_email:string, recipient_name:string}>
   *   Enabled ad hoc recipients.
   */
  protected function getAdHocRecipients(): array {
    $storage = $this->entityTypeManager->getStorage('no_active_users_recipient');
    $entities = $storage->loadMultiple();
    $recipients = [];

    foreach ($entities as $entity) {
      if (!$entity instanceof NoActiveUsersRecipientInterface || !$entity->isEnabled()) {
        continue;
      }

      $email = strtolower(trim($entity->getEmail()));
      if ($email === '') {
        continue;
      }

      $recipients[] = [
        'recipient_email' => $email,
        'recipient_name' => (string) $entity->label(),
      ];
    }

    return $recipients;
  }

  /**
   * Gets the configured recipient roles.
   *
   * @return string[]
   *   Role machine names.
   */
  protected function getConfiguredRecipientRoles(): array {
    $roles = $this->configFactory
      ->get('va_gov_notifications.settings')
      ->get('no_active_users_recipient_roles') ?: [];

    return array_values(array_filter(array_map('strval', $roles)));
  }

  /**
   * Dedupe sections by section id.
   *
   * @param array<int, array{section_id:int, section_name:string}> $sections
   *   Sections list.
   *
   * @return array<int, array{section_id:int, section_name:string}>
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
