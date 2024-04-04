<?php

declare(strict_types = 1);

namespace Drupal\expirable_content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A service that provides information about expirable content.
 */
final class ExpirableContentInformation implements ExpirableContentInformationInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs an ExpirableContentInformation object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity_type.manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public function isExpirableEntity(EntityInterface $entity): bool {
    $results = $this->entityTypeManager
      ->getStorage('expirable_content_type')
      ->loadByProperties([
        'entity_type' => $entity->getEntityTypeId(),
        'entity_bundle' => $entity->bundle(),
        'status' => TRUE,
      ]);
    return count($results) > 0;
  }

  /**
   * {@inheritDoc}
   */
  public function isExpirableEntityType(EntityTypeInterface $entityType): bool {
    $results = $this->entityTypeManager
      ->getStorage('expirable_content_type')
      ->loadByProperties([
        'entity_type' => $entityType->id(),
        'status' => TRUE,
      ]);
    return count($results) > 0;
  }

}
