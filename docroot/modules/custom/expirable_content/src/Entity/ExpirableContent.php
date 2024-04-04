<?php

declare(strict_types = 1);

namespace Drupal\expirable_content\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\expirable_content\ExpirableContentInterface;

/**
 * Defines the expirable content entity.
 *
 * @ContentEntityType(
 *   id = "expirable_content",
 *   label = @Translation("Expirable Content"),
 *   label_collection = @Translation("Expirable Content"),
 *   label_singular = @Translation("expirable content"),
 *   label_plural = @Translation("expirable content"),
 *   label_count = @PluralTranslation(
 *     singular = "@count expirable content",
 *     plural = "@count expirable content",
 *   ),
 *   bundle_label = @Translation("Expirable Content type"),
 *   handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   base_table = "expirable_content",
 *   revision_table = "expirable_content_revision",
 *   data_table = "expirable_content_field_data",
 *   revision_data_table = "expirable_content_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer expirable_content types",
 *   internal = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "bundle" = "bundle",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   bundle_entity_type = "expirable_content_type",
 * )
 *
 * @internal
 *   This entity is marked internal because it should not be used directly.
 *   Instead, the computed expiration and warning fields should be set on the
 *   entity directly.
 */
final class ExpirableContent extends ContentEntityBase implements ExpirableContentInterface {

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['expiration'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Expiration date'))
      ->setDescription(t('The date the content entity expires.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE);

    $fields['warning'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Warning date'))
      ->setDescription(t('The date a warning is established for the content entity.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE);

    $fields['content_entity_type_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Content entity type ID'))
      ->setDescription(t('The ID of the content entity type this record is for.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH)
      ->setRevisionable(TRUE);

    $fields['content_entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Content entity ID'))
      ->setDescription(t('The ID of the content entity this record is for.'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE);

    $fields['content_entity_revision_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Content entity revision ID'))
      ->setDescription(t('The revision ID of the content entity this record is for.'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE);

    return $fields;
  }

  /**
   * Load an Expirable Content entity from a given content entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The content entity.
   *
   * @return \Drupal\expirable_content\ExpirableContentInterface|null
   *   The expirable content for this entity or null if one cannot be found.
   *
   * @internal
   *   This method should only be called by code directly handling the
   *   ExpirableContent entity objects.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function loadFromEntity(EntityInterface $entity): ?ExpirableContentInterface {
    $expirable_content = NULL;
    /** @var \Drupal\expirable_content\ExpirableContentInformationInterface $info */
    $info = \Drupal::service('expirable_content.information');

    if ($info->isExpirableEntity($entity)) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $storage = \Drupal::entityTypeManager()->getStorage('expirable_content');

      // New entities may not have a loaded revision ID at this point, but the
      // creation of an expirable content entity may have already been
      // triggered elsewhere. In this case we have to match on the revision ID
      // (instead of the loaded revision ID).
      $revision_id = $entity->getLoadedRevisionId() ?: $entity->getRevisionId();
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('content_entity_type_id', $entity->getEntityTypeId())
        ->condition('content_entity_id', $entity->id())
        ->condition('content_entity_revision_id', $revision_id)
        ->allRevisions()
        ->execute();

      if ($ids) {
        /** @var \Drupal\expirable_content\Entity\ExpirableContent $expirable_content */
        $expirable_content = $storage->loadRevision(key($ids));
      }
    }

    return $expirable_content;
  }

}
