<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldFormatter;

use Drupal\entity_reference_hierarchy_revisions\Plugin\Field\FieldFormatter\EntityReferenceHierarchyRevisionsEntityFormatter;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "magichead_entity_view",
 *   label = @Translation("Magichead Rendered Entity"),
 *   description = @Translation("Display the referenced entities rendered by entity_view() with hierarchy."),
 *   field_types = {
 *     "magichead"
 *   }
 * )
 */
class MagicheadFormatter extends EntityReferenceHierarchyRevisionsEntityFormatter {}
