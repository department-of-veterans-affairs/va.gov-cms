<?php

namespace Drupal\va_gov_clone\Plugin\VAGov\CloneHandler;

use Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderBase;

/**
 * Office plugin for cloning.
 *
 * @CloneEntityFinder(
 *   id = "office",
 *   label = @Translation("VAMC Office Entites to Clone")
 * )
 */
class Office extends CloneEntityFinderBase {

  /**
   * {@inheritDoc}
   */
  public function getEntitiesToClone(int $office_tid): array {
    if (!$office_tid) {
      return [];
    }

    $ids = $this->getAllIdsToClone($office_tid);
    return $this->loadEntities($ids, 'node');
  }

  /**
   * Get all ids for a content type based upon the plugin.
   *
   * @return int[]
   *   Array of ids to clone.
   */
  protected function getAllIdsToClone(int $office_id) : array {
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
    $query->condition('field_administration.target_id', $office_id);
    return $query->execute();
  }

}
