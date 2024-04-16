<?php

namespace Drupal\va_gov_backend\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\va_gov_vamc\ServiceLocationMigration;

/**
 * Script using codit_batch_operations and va_gov_vamc script.
 */
class Vacms15559MigrateServiceLocationFromNodeToParagraph extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle():string {
    return 'Migrate VAMC Facility health service data to Service Location paragraph';
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchSize(): int {
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Successfully migrated @completed VAMC Facility health services out of @total.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    $source_bundle = 'health_care_local_health_service';
    $items = $this->getNidsOfType($source_bundle);
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    // Using ServiceLocationMigration, as it was already written prior
    // to adopting codit_batch_operations, but to prevent rework
    // while also providing persistent logging across script runs.
   if (empty($sandbox['migrator'])) {
     $sandbox['migrator'] = new ServiceLocationMigration();
   }
    $msg = $sandbox['migrator']->run($item, $sandbox);

    return $msg;
  }

}
