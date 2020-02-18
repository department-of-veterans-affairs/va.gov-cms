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
   *
   * The ignore comments are because PHPCS keeps warning about
   * "Possible useless method overriding detected" which I don't think is valid.
   */
  // @codingStandardsIgnoreStart
  public function __construct(EntityManagerInterface $entity_manager) {
    parent::__construct($entity_manager);
  }
  // @codingStandardsIgnoreEnd

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
