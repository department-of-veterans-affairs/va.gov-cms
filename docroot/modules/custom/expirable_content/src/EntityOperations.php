<?php

declare(strict_types=1);

namespace Drupal\expirable_content;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * Constructs a new EntityOperations object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
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
   * @see hook_entity_update()
   */
  public function entityUpdate(EntityInterface $entity) {
    // Do things when entity is updated.
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that was just saved.
   *
   * @see hook_entity_delete()
   */
  public function entityDelete(EntityInterface $entity) {
    // Do things when an entity is deleted.
  }

}
