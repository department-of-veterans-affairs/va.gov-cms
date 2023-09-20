<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Traits;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;

/**
 * Separate out entity display operations-specific methods.
 */
trait EntityDisplayOperationsTrait {

  /**
   * Get the entity type.
   *
   * @return string
   *   The entity type.
   */
  abstract public function getEntityType(): string;

  /**
   * Get the field name.
   *
   * @return string
   *   The field name.
   */
  abstract public function getFieldName(): string;

  /**
   * Get the entity display repository.
   *
   * @return \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   *   The entity display repository.
   */
  abstract protected function getEntityDisplayRepository(): EntityDisplayRepositoryInterface;

  /**
   * {@inheritDoc}
   */
  public function getFormModeOptions(string $bundle): array {
    $entityType = $this->getEntityType();
    return $this->getEntityDisplayRepository()->getFormModeOptionsByBundle($entityType, $bundle);
  }

  /**
   * {@inheritDoc}
   */
  public function getFormDisplayConfig(string $bundle, string $formMode): EntityFormDisplayInterface {
    $entityType = $this->getEntityType();
    return $this->getEntityDisplayRepository()->getFormDisplay($entityType, $bundle, $formMode);
  }

  /**
   * {@inheritDoc}
   */
  public function getViewModeOptions(string $bundle): array {
    $entityType = $this->getEntityType();
    return $this->getEntityDisplayRepository()->getViewModeOptionsByBundle($entityType, $bundle);
  }

  /**
   * {@inheritDoc}
   */
  public function getViewDisplayConfig(string $bundle, string $viewMode): EntityViewDisplayInterface {
    $entityType = $this->getEntityType();
    return $this->getEntityDisplayRepository()->getViewDisplay($entityType, $bundle, $viewMode);
  }

}
