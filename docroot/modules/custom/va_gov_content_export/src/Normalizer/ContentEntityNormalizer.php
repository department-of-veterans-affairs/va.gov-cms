<?php

namespace Drupal\va_gov_content_export\Normalizer;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\tome_sync\Normalizer\ContentEntityNormalizer as TomeSyncContentEntityNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Drupal\serialization\Normalizer\ContentEntityNormalizer as BaseContentEntityNormalizer;

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
   * ContentEntityNormalizer constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, DenormalizerInterface $inner_service) {
    $this->innerService = $inner_service;
    parent::__construct($entity_manager);
  }

  /**
   * Can't use `parent::parent` in PHP.
   *
   * @see https://stackoverflow.com/a/8212262/292408
   *
   * @param $entity
   * @param $format
   * @param $context
   *
   * @return array|bool|float|int|string|null
   */
  public function baseNormalize($entity, $format, $context) {
    return BaseContentEntityNormalizer::normalize($entity, $format, $context);
  }

  /**
   * @inheritDoc
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $values = $this->baseNormalize($entity, $format, $context);
    // Empty, to unnormalize Tome as we want the entity IDs.
    return $values;
  }

}
