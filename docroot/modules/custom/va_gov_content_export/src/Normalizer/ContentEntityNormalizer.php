<?php

namespace Drupal\va_gov_content_export\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer as BaseContentEntityNormalizer;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 *
 * @internal
 */
class ContentEntityNormalizer extends BaseContentEntityNormalizer {

  /**
   * ContentEntityNormalizer constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    parent::__construct($entity_manager);
   }

  /**
   * @inheritDoc
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $values = parent::normalize($entity, $format, $context);

    // Empty, to unnormalize Tome as we want the entity IDs.
    $breadcrumb_values = $this->getBreadCrumbsForValues($entity);
    if ($breadcrumb_values) {
      $values['entityUrl']['breadcrumb'] = $breadcrumb_values;
      $values['entityUrl']['path'] = $entity->url();
    }

    return $values;
  }

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
          'routed' => $breadcrumb->getUrl()->isRouted()
        ],
        'text' => $breadcrumb->getText(),
      ];
    }

    return $breadcrumb_content;
  }
}
