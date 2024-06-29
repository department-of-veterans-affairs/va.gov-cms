<?php

namespace Drupal\va_gov_backend\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\va_gov_vamc\ServiceLocationMigration;

require_once __DIR__ . '/../../../../../scripts/content/script-library.php';


/**
 * A test and example Batch operation script to show processing.
 */
class vacms15559MigrateServiceLocationFromNodeToParagraph extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle():string {
    // Return a meaningful title for this script to help differentiate one script
    // from another.
    return 'VACMS-15559: Migrate Service Location from node to paragraph';
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchSize(): int {
    // For heavy operations like loading and altering hundreds of nodes, set a
    // larger batch size.  For a small number of items, you can set the batch
    // size to 1.  When in doubt return 1.
    return 1000;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    // Return a meaningful message to display at the end of the run.
    // This message can include the tokens '@completed' and '@total'.
    return 'Completed processing @completed of @total items.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    // This is where you do your database queries to get the primary list of
    // things to operate on.

    // The key in the key value pair must be unique, because we use that to
    // remove it from the array after it has been processed.
    // The value is the item to operate on.  It might be a node id if you are
    // loading the nodes in processOne() or it could be the node entities
    // already loaded.  Use care here.  If you have thousands of entities to
    // process, don't load them all upfront.  If you had a 100, you could load
    // them all at once here.  It would make the process faster.
    $source_bundle = 'health_care_local_health_service';
    $items = $this->getNidsOfType($source_bundle, FALSE);
    // We want this process to move quickly, so no dedupe check
    script_library_skip_post_api_data_check(TRUE);

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    // Do your actions here.
    //  - Update an entity.
    //  - Move a node from one content type to another.
    //  - Whatever magical thing needs doing to ONE item from your list.
    // Then return a message about what was done.
    // If you return a non-empty message, it will get logged in the BatchOpLog.


    // If you are doing a big process and wanted to add to the log or errors
    // you can log specifically as you go.
    $migrator = new ServiceLocationMigration();
    $msg = $migrator->run($sandbox, $item);

    return $msg;
  }

}
