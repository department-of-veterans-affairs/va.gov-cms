<?php

namespace Drupal\expirable_content;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * A custom field storage definition for bundle fields that are not base fields.
 *
 * For convenience, we extend from BaseFieldDefinition although this should not
 * implement FieldDefinitionInterface.
 *
 * @see https://www.drupal.org/node/2280639.
 */
class FieldStorageDefinition extends BaseFieldDefinition {

  /**
   * {@inheritdoc}
   */
  public function isBaseField() {
    return FALSE;
  }

}
