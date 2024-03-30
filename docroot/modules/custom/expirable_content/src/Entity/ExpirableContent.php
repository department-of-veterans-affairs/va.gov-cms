<?php

declare(strict_types = 1);

namespace Drupal\expirable_content\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\expirable_content\ExpirableContentInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the expirable content entity class.
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
 *     "list_builder" = "Drupal\expirable_content\ExpirableContentListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\expirable_content\Form\ExpirableContentForm",
 *       "edit" = "Drupal\expirable_content\Form\ExpirableContentForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\expirable_content\Routing\ExpirableContentHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "expirable_content",
 *   revision_table = "expirable_content_revision",
 *   data_table = "expirable_content_field_data",
 *   revision_data_table = "expirable_content_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer expirable_content types",
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *     "revision" = "revision_id",
 *     "bundle" = "bundle",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "collection" = "/admin/content/expirable-content",
 *     "add-form" = "/expirable-content/add/{expirable_content_type}",
 *     "add-page" = "/expirable-content/add",
 *     "canonical" = "/expirable-content/{expirable_content}",
 *     "edit-form" = "/expirable-content/{expirable_content}",
 *     "delete-form" = "/expirable-content/{expirable_content}/delete",
 *     "delete-multiple-form" = "/admin/content/expirable-content/delete-multiple",
 *   },
 *   bundle_entity_type = "expirable_content_type",
 *   field_ui_base_route = "entity.expirable_content_type.edit_form",
 * )
 */
final class ExpirableContent extends ContentEntityBase implements ExpirableContentInterface, EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += ExpirableContent::ownerBaseFieldDefinitions($entity_type);

    $fields['uid']
      ->setLabel(t('User'))
      ->setDescription(t('The username of the entity creator.'))
      ->setRevisionable(TRUE);

    $fields['expirable_content_type_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Expirable content type id.'))
      ->setDescription(t('The id of the expirable content type.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    $fields['content_entity_type_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Content entity type ID'))
      ->setDescription(t('The ID of the content entity type this moderation state is for.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH)
      ->setRevisionable(TRUE);

    $fields['content_entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Content entity ID'))
      ->setDescription(t('The ID of the content entity this moderation state is for.'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE);

    $fields['content_entity_revision_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Content entity revision ID'))
      ->setDescription(t('The revision ID of the content entity this moderation state is for.'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE);

    return $fields;
  }

}
