<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;
use Drupal\va_gov_content_types\Exception\NoOriginalExistsException;

/**
 * Provides an interface for retrieving the original version of a node.
 */
trait GetOriginalTrait {

  /**
   * {@inheritDoc}
   */
  abstract public function get($fieldName);

  /**
   * Indicates whether the node has an original version.
   *
   * @return bool
   *   TRUE if the node has an original version.  FALSE otherwise.
   */
  public function hasOriginalVersion(): bool {
    /** @var mixed $node */
    $node = $this;
    return isset($node->original) && $node->original instanceof VaNodeInterface;
  }

  /**
   * Retrieve the original version of this node.
   *
   * @return \Drupal\va_gov_content_types\Entity\VaNodeInterface
   *   The original version of this node.
   *
   * @throws \Drupal\va_gov_content_types\Exception\NoOriginalExistsException
   *   Thrown when the node has no original version.
   */
  public function getOriginalVersion(): VaNodeInterface {
    if (!$this->hasOriginalVersion()) {
      throw new NoOriginalExistsException('No original version exists for this node.');
    }
    /** @var mixed $node */
    $node = $this;
    /** @var \Drupal\va_gov_content_types\Entity\VaNodeInterface $original */
    $original = $node->original;
    return $original;
  }

  /**
   * Get the previously saved value of a field.
   *
   * @param string $fieldName
   *   The machine name of the field to get.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   The fieldItemList of the field.
   */
  public function getOriginalField(string $fieldName): FieldItemListInterface {
    $original = $this->getOriginalVersion();
    return $original->get($fieldName);
  }

  /**
   * Checks if the value of the field on the node changed.
   *
   * @param string $fieldName
   *   The machine name of the field to check.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  public function didChangeField(string $fieldName): bool {
    // These will each throw their own exceptions, which is what we want.
    // Don't make these defensive.
    $originalField = $this->getOriginalField($fieldName);
    $currentField = $this->get($fieldName);

    return !$currentField->equals($originalField);
  }

}
