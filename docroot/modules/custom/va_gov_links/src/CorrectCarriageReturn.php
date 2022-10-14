<?php

declare(strict_types = 1);

namespace Drupal\va_gov_links;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class for reacting to entity events.
 */
class CorrectCarriageReturn implements ContainerInjectionInterface {

  /**
   * Field types to replace links in.
   */
  protected const FIELD_TYPES = [
    'text',
    'text_long',
    'text_with_summary',
  ];

  /**
   * Constructs a new LinkyReplacerEntityOperations.
   */
  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    );
  }

  /**
   * Implements hook_entity_presave().
   */
  public function entityPreSave(EntityInterface $entity): void {
    if (!$entity instanceof FieldableEntityInterface) {
      return;
    }

    foreach ($entity as $fieldName => $fieldList) {
      assert($fieldList instanceof FieldItemListInterface);
      if (in_array($fieldList->getFieldDefinition()->getType(), static::FIELD_TYPES, TRUE)) {
        foreach ($fieldList as $item) {
          $item->value = str_replace('&#13;', "\r", $item->value);
        }
      }
    }
  }

}
