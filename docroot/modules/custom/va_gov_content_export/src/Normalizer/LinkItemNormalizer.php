<?php

namespace Drupal\va_gov_content_export\Normalizer;

use Drupal\Component\Serialization\Json;
use Drupal\link\LinkItemInterface;
use Drupal\link\Plugin\Field\FieldType\LinkItem;
use Drupal\serialization\Normalizer\FieldItemNormalizer;

/**
 * Normalizer for Link Items.
 */
class LinkItemNormalizer extends FieldItemNormalizer {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = LinkItemInterface::class;

  /**
   * {@inheritDoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $attributes = parent::normalize($object, $format, $context);
    /** @var \Drupal\link\LinkItemInterface $object */
    if ($format === Json::getFileExtension()) {
      $attributes[LinkItem::mainPropertyName()] = $object->getUrl()->toString();
    }
    return $attributes;
  }

}
