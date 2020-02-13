<?php

namespace Drupal\va_gov_content_export\Normalizer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\tome_sync\Normalizer\ContentEntityNormalizer as TomeSyncContentEntityNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer as BaseContentEntityNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 *
 * @internal
 */
class ContentEntityNormalizer extends TomeSyncContentEntityNormalizer {

  /**
   * Field names that should be excluded from normalization.
   *
   * Should only be used when more generic logic cannot be used.
   *
   * @var array
   */
  protected $fieldDenyList = [
    // Add metatags back in. Leave this commented out.
    // 'metatag',
  ];

  /**
   * @var NormalizerInterface
   */
  protected $innerService;

  /**
   * ContentEntityNormalizer constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, NormalizerInterface $inner_service) {
    $this->innerService = $inner_service;
    parent::__construct($entity_manager);
   }

  /**
   * @inheritDoc
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $values = $this->innerService->normalize($entity, $format, $context);

    // Empty, to unnormalize Tome as we want the entity IDs.
    $breadcrumb_values = $this->getBreadCrumbsForValues($entity);
    if ($breadcrumb_values) {
      $values['entityUrl']['breadcrumb'] = $breadcrumb_values;
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
          'path' => $breadcrumb->getUrl()->getUri(),
        ],
        'text' => $breadcrumb->getText(),
      ];
    }

    return $breadcrumb_content;
  }
}
