<?php

namespace Drupal\va_gov_live_field_migration\FieldPurger;

/**
 * The field purger service.
 */
class FieldPurger implements FieldPurgerInterface {

  /**
   * {@inheritdoc}
   */
  public function purge(int $batchSize = 100): void {
    field_purge_batch($batchSize);
  }

}
