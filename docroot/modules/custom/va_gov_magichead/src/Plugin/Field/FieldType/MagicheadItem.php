<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldType;

use Drupal\entity_reference_hierarchy\Plugin\Field\FieldType\EntityReferenceHierarchyItem;

/**
 * Defines the 'magichead' field type.
 *
 * @FieldType(
 *   id = "magichead",
 *   label = @Translation("Magichead"),
 *   category = @Translation("Reference"),
 *   default_widget = "entity_reference_hierarchy_autocomplete",
 *   default_formatter = "entity_reference_label",
 *   list_class = "\Drupal\entity_reference_hierarchy\EntityReferenceHierarchyFieldItemList",
 * )
 */
class MagicheadItem extends EntityReferenceHierarchyItem {

}
