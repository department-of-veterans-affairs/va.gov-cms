<?php

namespace Drupal\va_gov_content_export\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer as BaseContentEntityNormalizer;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 *
 * @internal
 */
class ContentEntityNormalizer extends BaseContentEntityNormalizer {

  /**
   * Normalize values that Tome either removes or does not add.
   *
   * @inheritDoc
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    // Part of the normalize() here adds back the entity IDs by overriding
    // Tome's normalize() which does the removal. It is invisible, this comment
    // serves as the code here.
    $values = parent::normalize($entity, $format, $context);

    $breadcrumb_values = $this->getBreadCrumbsForValues($entity);
    if ($breadcrumb_values) {
      $values['entityUrl']['breadcrumb'] = $breadcrumb_values;
      $values['entityUrl']['path'] = $entity->url();
    }
    if (isset($entity->values['reverse_entity_references']) && is_array($entity->values['reverse_entity_references'])) {
      // This pseudo field exists, so add its data.
      foreach ($entity->values['reverse_entity_references'] as $reverse_field_name => $reverse_entity_reference) {
        $values[$reverse_field_name] = $reverse_entity_reference;
      }

    }

    return $values;
  }

  /**
   * Get breadcrumbs for an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to get breadcrumbs for.
   *
   * @return array
   *   Array of breadcrumbs.
   */
  protected function getBreadCrumbsForValues(ContentEntityInterface $entity) : array {
    if (!$entity->breadcrumbs) {
      return [];
    }

    $breadcrumb_content = [];
    /** @var \Drupal\Core\Link $breadcrumb */
    foreach ($entity->breadcrumbs as $breadcrumb) {
      $breadcrumb_content[] = [
        'url' => [
          'path' => $breadcrumb->getUrl()->toString(),
          'routed' => $breadcrumb->getUrl()->isRouted(),
        ],
        'text' => $breadcrumb->getText(),
      ];
    }

    return $breadcrumb_content;
  }

}
