<?php

namespace Drupal\va_gov_live_field_migration\Migrator\Traits;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;

/**
 * Separate out field-storage-operations-specific methods.
 */
interface EntityDisplayOperationsInterface {

  /**
   * Get the form mode options for the bundle.
   *
   * @param string $bundle
   *   The bundle.
   *
   * @return array
   *   The form mode options.
   */
  public function getFormModeOptions(string $bundle): array;

  /**
   * Get the form display config.
   *
   * @param string $bundle
   *   The bundle.
   * @param string $formMode
   *   The form mode.
   *
   * @return \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   *   The form display config.
   */
  public function getFormDisplayConfig(string $bundle, string $formMode): EntityFormDisplayInterface;

  /**
   * Get the view mode options for the entity type and bundle.
   *
   * @param string $bundle
   *   The bundle.
   *
   * @return array
   *   The view mode options.
   */
  public function getViewModeOptions(string $bundle): array;

  /**
   * Get the view display config.
   *
   * @param string $bundle
   *   The bundle.
   * @param string $viewMode
   *   The view mode.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   *   The view display config.
   */
  public function getViewDisplayConfig(string $bundle, string $viewMode): EntityViewDisplayInterface;

}
