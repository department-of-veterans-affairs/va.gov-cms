<?php

namespace Drupal\entity_reference_hierarchy\Plugin\Field\FieldFormatter;

use Drupal\entity_reference_hierarchy\Plugin\Field\FieldFormatter\EntityReferenceHierarchyEntityFormatter;

/**
 * Plugin implementation of the 'magichead' formatter.
 *
 * @FieldFormatter(
 *   id = "magichead_entity_view",
 *   label = @Translation("Magichead rendered entity hierarchy"),
 *   description = @Translation("Display referenced entities rendered by entity_view() in hierarchical tree."),
 *   field_types = {
 *     "magichead"
 *   }
 * )
 */
class MagicheadEntityFormatter extends EntityReferenceHierarchyEntityFormatter {}
