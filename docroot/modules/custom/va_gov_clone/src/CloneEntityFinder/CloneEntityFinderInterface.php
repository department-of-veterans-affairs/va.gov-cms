<?php

namespace Drupal\va_gov_clone\CloneEntityFinder;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Clone handler plugins.
 */
interface CloneEntityFinderInterface extends PluginInspectionInterface {

  /**
   * Get Entities to clone.
   *
   * @param int $section_id
   *   The section tid.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of Entities.
   */
  public function getEntitiesToClone(int $section_id) : array;

}
