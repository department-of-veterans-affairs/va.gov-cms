<?php

namespace Drupal\va_gov_magichead\EventSubscriber;

use Drupal\core_event_dispatcher\FormHookEvents; //check this

/**
 * Alter bundle field definitions.
 *
 * Implements hook_entity_bundle_field_info_alter.
 *
 * @param \Drupal\Core\Field\FieldDefinitionInterface[] $fields
 *   The array of bundle field definitions.
 * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
 *   The entity type definition.
 * @param string $bundle
 *   The bundle.
 */
function va_gov_magichead_entity_bundle_field_info_alter(&$fields, EntityTypeInterface $entity_type, $bundle) {
  // Use a different entity ID so that this will work wherever it's used
  if ($bundle === 'taxonomy-term-va-benefits-taxonomy-form') {
    if (!empty($fields['thatField']))
      $fields['thatField']->addConstraint(
        MagicheadDepthFieldConstraint::class
      );
  }
}
