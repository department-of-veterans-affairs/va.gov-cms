<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\node\NodeInterface;

/**
 * Interface ExclusionTypesInterface.
 */
interface ExclusionTypesInterface {

  /**
   * Get stored excluded types config.
   *
   * @return array
   *   Array of types that have been excluded.
   */
  public function getExcludedTypes() : array;

  /**
   * Get Json array of excluded types.
   *
   * @return string
   *   Json array.
   */
  public function getJson() : string;

  /**
   * Determine whether a bundle is excluded from the front end.
   *
   * @return bool
   *   Whether or not the bundle id is excluded.
   */
  public function typeIsExcluded(String $bundle) : bool;

  /**
   * Determine whether the node has an excluded type.
   * 
   * 
   * 
   * @return bool
   *   Whether or not the Node's bundle is excluded.
   */
  public function hasExcludedType(NodeInterface $node): bool;

}
