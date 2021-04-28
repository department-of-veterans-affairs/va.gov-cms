<?php

namespace Drupal\va_gov_migrate\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands related to migrations.
 */
class Commands extends DrushCommands {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    Connection $database,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct();
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Clean up bad node revisions.
   *
   * @command va:gov-clean-revs
   * @aliases vg-cr,va-gov-clean-revs
   */
  public function cleanRevs() {
    $time = 1572551719;

    $query = $this->database->select('node_revision', 'nr');
    $query->condition('revision_timestamp', $time);
    $query->fields('nr', ['vid']);
    $vids = $query->execute()->fetchCol();

    $query = $this->database->select('node_revision', 'nr');
    $query->condition('revision_log', 'Update of status by migration.');
    $query->condition('nid', 1884);
    $query->fields('nr', ['vid']);
    $more_vids = $query->execute()->fetchCol();

    $vids = array_merge($vids, $more_vids);
    $this->logger->info('Attempting to Delete ' . count($vids) . ' node revisions');
    $missed_vids = [];
    foreach ($vids as $vid) {
      try {
        $this->logger->info('Deleting: ' . $vid);
        $this->entityTypeManager->getStorage('node')->deleteRevision($vid);
      }
      catch (EntityStorageException $e) {
        $this->logger->warning('Vid ' . $vid . ' could not be deleted: ' . $e->getMessage());
        $missed_vids[] = $vid;
      }
    }

    $count = count($vids) - count($missed_vids);
    $this->logger->success('Deleted ' . $count . ' revision(s).');
    $this->logger->warning('The following revisions were not deleted: ' . implode(', ', $missed_vids));
  }

}
