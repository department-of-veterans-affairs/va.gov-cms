<?php

namespace Drupal\va_gov_live_field_migration\FieldPurger;

/**
 * An interface for the field purger service.
 */
interface FieldPurgerInterface {

  /**
   * Purges a batch of deleted Field API data, field storages, or fields.
   *
   * @param int $batchSize
   *   The maximum number of field data records to purge before returning.
   */
  public function purge(int $batchSize = 100): void;

}
