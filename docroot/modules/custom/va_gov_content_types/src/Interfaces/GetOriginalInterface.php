<?php

namespace Drupal\va_gov_content_types\Interfaces;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * Provides an interface for retrieving the original version of a node.
 */
interface GetOriginalInterface {

  /**
   * Indicates whether the node has an original version.
   *
   * @return bool
   *   TRUE if the node has an original version.  FALSE otherwise.
   */
  public function hasOriginal(): bool;

  /**
   * Retrieve the original version of this node.
   *
   * @return \Drupal\va_gov_content_types\Entity\VaNodeInterface
   *   The original version of this node.
   *
   * @throws \Drupal\va_gov_content_types\Exception\NoOriginalExistsException
   *   Thrown when the node has no original version.
   */
  public function getOriginal(): VaNodeInterface;

  /**
   * Get the previously saved value of a field.
   *
   * @param string $field_name
   *   The machine name of the field to get.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   The value of the field.
   */
  public function getOriginalField(string $field_name): FieldItemListInterface;

  /**
   * Checks if the value of the field on the node changed.
   *
   * @param string $fieldName
   *   The machine name of the field to check.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  public function didChangeField(string $fieldName): bool;

}
