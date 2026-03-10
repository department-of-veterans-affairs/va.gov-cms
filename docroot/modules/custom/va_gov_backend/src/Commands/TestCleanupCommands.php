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
      $query = $storage->getQuery()->accessCheck(FALSE);
      if ($entity_type === 'user') {
        $query = $storage->getQuery()
          ->condition($title_field, 'test__%', 'LIKE')
          ->accessCheck(FALSE);
      }
      else {
        $or = $query->orConditionGroup()
          ->condition($title_field, '[Test Data]%', 'LIKE')
          ->condition($title_field, 'polygon_image.png', '=');
        $query->condition($or);
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

}
