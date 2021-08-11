<?php

namespace Drupal\va_gov_clone\Plugin\VAGov\CloneHandler;

use Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderBase;

/**
 * Campaign Landing Page plugins for Cloning.
 *
 * @CloneEntityFinder(
 *   id = "clp",
 *   label = @Translation("Campaign Landing Page")
 * )
 */
class CLP extends CloneEntityFinderBase {

  /**
   * {@inheritDoc}
   */
  public function getEntitiesToClone(int $section_id): array {
    $ids = $this->getAllIdsToClone();
    return $this->loadEntities($ids, 'node');
  }

  /**
   * Get all ids for a content type based upon the plugin.
   *
   * @return int[]
   *   Array of ids to clone.
   */
  protected function getAllIdsToClone() : array {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->accessCheck(FALSE);
    $query->condition('type', 'campaign_landing_page');
    return $query->execute();
  }

}
