<?php

namespace Drupal\va_gov_clone\Plugin\VAGov\CloneHandler;

use Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderBase;

/**
 * Office plugin for cloning.
 *
 * @CloneEntityFinder(
 *   id = "section",
 *   label = @Translation("Section Entites to Clone")
 * )
 */
class Section extends CloneEntityFinderBase {

  /**
   * {@inheritDoc}
   */
  public function getEntitiesToClone(int $section_id): array {
    if (!$section_id) {
      return [];
    }

    $ids = $this->getAllIdsToClone($section_id);
    return $this->loadEntities($ids, 'node');
  }

  /**
   * Get all ids for a content type based upon the plugin.
   *
   * @return int[]
   *   Array of ids to clone.
   */
  protected function getAllIdsToClone(int $section_id) : array {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->accessCheck(FALSE);
    $query->condition(
      'type',
      [
        'detail',
        'page',
        'press_release', '
        event', '
        vet_center_facility_health_servi',
      ],
      'IN'
    );
    $query->condition('field_administration.target_id', $section_id);
    return $query->execute();
  }

}
