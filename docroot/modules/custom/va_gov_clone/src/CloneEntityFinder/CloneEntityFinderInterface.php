<?php

namespace Drupal\va_gov_clone\CloneHandler;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Clone handler plugins.
 */
interface CloneEntityFinderInterface extends PluginInspectionInterface {

  /**
   * Get Entities to clone.
   *
   * @param int $office_tid
   *   The office tid.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of Entities.
   */
  public function getEntitiesToClone(int $office_tid) : array;

}
