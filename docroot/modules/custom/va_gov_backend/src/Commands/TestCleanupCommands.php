<?php

namespace Drupal\va_gov_backend\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for test data cleanup.
 */
class TestCleanupCommands extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a TestCleanupCommands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Delete all test data entities.
   *
   * @command va-test-cleanup
   * @aliases va-tc
   * @usage va-test-cleanup
   *   Delete all entities with [Test Data] prefix
   * @usage va-test-cleanup --dry-run
   *   Show what would be deleted without deleting
   * @option dry-run Show what would be deleted without actually deleting
   */
  public function cleanupTestData($options = ['dry-run' => FALSE]) {
    $dry_run = $options['dry-run'];
    $deleted_count = [];

    // Define entity types and their title fields.
    $entity_configs = [
      'node' => 'title',
      'media' => 'name',
      'taxonomy_term' => 'name',
      'user' => 'name',
    ];

    foreach ($entity_configs as $entity_type => $title_field) {
      $storage = $this->entityTypeManager->getStorage($entity_type);

      // Query for entities with [Test Data] prefix.
      $query = $storage->getQuery()
        ->condition($title_field, '[Test Data]%', 'LIKE')
        ->accessCheck(FALSE);
      if ($entity_type === 'user') {
        $query = $storage->getQuery()
          ->condition($title_field, 'test__%', 'LIKE')
          ->accessCheck(FALSE);
      }
      $ids = $query->execute();

      if (empty($ids)) {
        $this->io()->writeln("No test {$entity_type} entities found.");
        continue;
      }

      $count = count($ids);

      if ($dry_run) {
        $this->io()->writeln("<comment>Would delete {$count} {$entity_type} entities (dry run)</comment>");
        // Load and display titles.
        $entities = $storage->loadMultiple($ids);
        foreach ($entities as $entity) {
          $this->io()->writeln("  - {$entity->label()} (ID: {$entity->id()})");
        }
      }
      else {
        $entities = $storage->loadMultiple($ids);
        foreach ($entities as $entity) {
          try {
            if (isset($entity->_contentModerationState)) {
              $entity->_contentModerationState->delete();
            }
            $entity->delete();
          }
          catch (\Exception $e) {
            $this->logger()->error("Failed to delete {$entity_type} {$entity->id()}: " . $e->getMessage());
          }
        }
        $this->io()->writeln("<info>Deleted {$count} {$entity_type} entities</info>");
        $deleted_count[$entity_type] = $count;
      }
    }

    if (!$dry_run && !empty($deleted_count)) {
      $total = array_sum($deleted_count);
      $this->io()->writeln("<info>Total entities deleted: {$total}</info>");
    }
  }

  /**
   * Delete test data by date range.
   *
   * @param string $date
   *   The date (YYYY-MM-DD) to delete entities created after.
   *
   * @command va-test-cleanup-by-date
   *
   * @usage va-test-cleanup-by-date 2026-02-23
   *   Delete all test entities created after Feb 23, 2026
   */
  public function cleanupTestDataByDate($date) {
    $timestamp = strtotime($date);

    if ($timestamp === FALSE) {
      $this->io()->writeln("<error>Invalid date format. Use YYYY-MM-DD</error>");
      return;
    }

    $entity_types = ['node', 'media', 'taxonomy_term'];

    foreach ($entity_types as $entity_type) {
      $storage = $this->entityTypeManager->getStorage($entity_type);

      $query = $storage->getQuery()
        ->condition('created', $timestamp, '>=')
        ->accessCheck(FALSE);

      // Also check for test data prefix.
      if ($entity_type === 'node') {
        $query->condition('title', '[Test Data]%', 'LIKE');
      }
      elseif ($entity_type === 'media') {
        $query->condition('name', '[Test Data]%', 'LIKE');
      }

      $ids = $query->execute();

      if (!empty($ids)) {
        $entities = $storage->loadMultiple($ids);
        foreach ($entities as $entity) {
          $entity->delete();
        }
        $count = count($ids);
        $this->io()->writeln("<info>Deleted {$count} {$entity_type} entities created after {$date}</info>");
      }
    }
  }

}
