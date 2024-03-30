<?php

declare(strict_types=1);

namespace Drupal\expirable_content;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides runtime entity operation hooks for expirable_content.
 */
class EntityOperations implements ContainerInjectionInterface {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container) {
    // TODO: Implement create() method.
    return new static($container->get('entity_type.manager'));
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that was just saved.
   *
   * @see hook_entity_insert()
   */
  public static function insert(ContentEntityInterface $entity) {
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that was just saved.
   *
   * @see hook_entity_insert()
   */
  public function entityInsert(EntityInterface $entity) {
    // Do things when an entity is inserted.
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that was just saved.
   *
   * @see hook_entity_insert()
   */
  public function entityUpdate(EntityInterface $entity) {
    // Do things when entity is updated.
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that was just saved.
   *
   * @see hook_entity_insert()
   */
  public function entityDelete(EntityInterface $entity) {
    // Do things when an entity is deleted.
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity revision being deleted.
   *
   * @see hook_entity_revision_delete()
   */
  public function entityRevisionDelete(EntityInterface $entity) {
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $translation
   *   The entity translation being deleted.
   *
   * @see hook_entity_translation_delete()
   */
  public function entityTranslationDelete(EntityInterface $translation) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
    if (!$translation->isDefaultTranslation()) {
      $langcode = $translation->language()->getId();
//      $content_moderation_state = ContentModerationStateEntity::loadFromModeratedEntity($translation);
//      if ($content_moderation_state && $content_moderation_state->hasTranslation($langcode)) {
//        $content_moderation_state->removeTranslation($langcode);
//        ContentModerationStateEntity::updateOrCreateFromEntity($content_moderation_state);
//      }
    }
  }

  /**
   * Creates or updates the moderation state of an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to update or create a moderation state for.
   */
  protected function createOrUpdateFromEntity() {}

}
