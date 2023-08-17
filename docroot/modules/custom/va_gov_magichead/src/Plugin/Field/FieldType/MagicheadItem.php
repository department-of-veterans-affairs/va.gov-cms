<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldType;

use Drupal\entity_reference_hierarchy_revisions\Plugin\Field\FieldType\EntityReferenceHierarchyRevisionsItem;

/**
 * Defines the 'magichead' field type.
 *
 * @FieldType(
 *   id = "magichead",
 *   label = @Translation("Magichead"),
 *   category = @Translation("Reference revisions"),
 *   default_widget = "magichead_paragraphs_classic",
 *   default_formatter = "entity_reference_label",
 *   list_class = "\Drupal\va_gov_magichead\MagicheadFieldItemList",
 * )
 */
class MagicheadItem extends EntityReferenceHierarchyRevisionsItem {}
