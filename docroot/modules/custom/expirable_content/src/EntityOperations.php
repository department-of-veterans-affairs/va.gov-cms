<?php

declare(strict_types=1);

namespace Drupal\expirable_content;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\expirable_content\Entity\ExpirableContent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides runtime entity operation hooks for expirable_content.
 */
class EntityOperations implements ContainerInjectionInterface {

  /**
   * The entity type manager service.
   *=
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The Expirable Content Information service.
   *
   * @var \Drupal\expirable_content\ExpirableContentInformationInterface
   */
  protected ExpirableContentInformationInterface $expirableContentInfo;

  /**
   * Constructs a new EntityOperations object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\expirable_content\ExpirableContentInformationInterface $expirableContentInformation
   *   The expirable content information service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ExpirableContentInformationInterface $expirableContentInformation) {
    $this->entityTypeManager = $entityTypeManager;
    $this->expirableContentInfo = $expirableContentInformation;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('expirable_content.information')
    );
  }

  /**
   * Acts on entities being inserted.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that was just saved.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see hook_entity_insert()
   */
  public function entityInsert(EntityInterface $entity) {
    $this->updateOrCreateFromEntity($entity);
  }

  /**
   * Acts on entities being updated.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that was just saved.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see hook_entity_update()
   */
  public function entityUpdate(EntityInterface $entity) {
    $this->updateOrCreateFromEntity($entity);
  }

  /**
   * Acts on entities being deleted.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that was deleted.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see hook_entity_delete()
   */
  public function entityDelete(EntityInterface $entity) {
    ExpirableContent::loadFromEntity($entity)?->delete();
  }

  /**
   * Acts on entity revisions being deleted.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity with the revision being deleted.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see hook_entity_revision_delete()
   */
  public function entityRevisionDelete(EntityInterface $entity) {
    if ($expirable_content = ExpirableContent::loadFromEntity($entity)) {
      if ($expirable_content->isDefaultRevision()) {
        $expirable_content->delete();
      }
      else {
        $this->entityTypeManager
          ->getStorage('expirable_content')
          ->deleteRevision($expirable_content->getRevisionId());
      }
    }
  }

  /**
   * Updates or creates expirable content information for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The expirable entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function updateOrCreateFromEntity(EntityInterface $entity): void {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    if ($this->expirableContentInfo->isExpirableEntity($entity)) {
      $expirable_content = ExpirableContent::loadFromEntity($entity);
      /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage('expirable_content');
      if (!($expirable_content instanceof ExpirableContent)) {
        $expirable_content = $storage->create([
          'content_entity_type_id' => $entity->getEntityTypeId(),
          'content_entity_id' => $entity->id(),
          'content_entity_revision_id' => $entity->getRevisionId(),
          'langcode' => $entity->language()->getId(),
          'bundle' => $entity->getEntityTypeId() . '.' . $entity->bundle(),
        ]);
      }
      // If a new revision of the content has been created, add a new expirable
      // content entity revision.
      if (!$expirable_content->isNew() && $expirable_content->content_entity_revision_id->value != $entity->getRevisionId()) {
        $expirable_content = $storage->createRevision($expirable_content, $entity->isDefaultRevision());
      }

      // Ensure expiration and warning dates are updated.
      $expirable_content->set('expiration', $entity->expiration_date->value);
      $expirable_content->set('warning', $entity->warning_date->value);
      $expirable_content->save();
    }
  }

}
