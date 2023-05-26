<?php

namespace Drupal\va_gov_facilities;

use Drupal\node\NodeInterface;

/**
 * Wrapper class of largely static helper functions related to Facilities.
 */
class FacilityOps {

  /**
   * Checks if the node is a facility.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility. FALSE otherwise.
   */
  public static function isFacility(NodeInterface $node) : bool {
    $facilities = [
      'health_care_local_facility',
      'nca_facility',
      'vba_facility',
      'vet_center_cap',
      'vet_center_mobile_vet_center',
      'vet_center_outstation',
      'vet_center',
    ];
    return in_array($node->bundle(), $facilities);
  }

  /**
   * Checks if the entity is a facility node with status info.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility with status info. FALSE otherwise.
   */
  public static function isFacilityWithoutStatus(NodeInterface $node) : bool {
    $facilities_without_status = [
      'vet_center_mobile_vet_center',
    ];
    return in_array($node->bundle(), $facilities_without_status);
  }

  /**
   * Checks if the entity is a facility node with status info.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility with status info. FALSE otherwise.
   */
  public static function isFacilityWithStatus(NodeInterface $node) : bool {
    return self::isBundleFacilityWithStatus($node->bundle());
  }

  /**
   * Checks if a bundle type has status info.
   *
   * @param string $type
   *   The bundle id to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility bundle with status info. FALSE otherwise.
   */
  public static function isBundleFacilityWithStatus(string $type) : bool {
    $facilities_with_status = [
      'health_care_local_facility',
      'nca_facility',
      'vba_facility',
      'vet_center_cap',
      'vet_center_outstation',
      'vet_center',
    ];

    return in_array($type, $facilities_with_status);
  }

  /**
   * Checks if the entity is a facility node with supplemental status.
   *
   * @param string $type
   *   The bundle id to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility bundle with status info. FALSE otherwise.
   */
  public static function isBundleFacilityWithSupplementalStatus(string $type) : bool {
    $facilities_with_supplemental_status = [
      'health_care_local_facility',
    ];

    return in_array($type, $facilities_with_supplemental_status);
  }

  /**
   * Check if the entity is a facility of a product that has launched.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility of a product that has launched. FALSE otherwise.
   */
  public static function isFacilityLaunched(NodeInterface $node) : bool {
    $facilities_not_launched = [
      'nca_facility',
      'vba_facility',
    ];
    return !in_array($node->bundle(), $facilities_not_launched);
  }

  /**
   * Checks if the entity is a facility that has a FE page.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility that has a FE page. FALSE otherwise.
   */
  public static function facilityHasFePage(NodeInterface $node) : bool {
    $facilities_with_no_fe_page = [
      'vet_center_cap',
      'vet_center_mobile_vet_center',
      'vet_center_outstation',
    ];
    return !in_array($node->bundle(), $facilities_with_no_fe_page);
  }

  /**
   * Get the parent-like field name.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to evaluate.
   *
   * @return string|null
   *   The machine name of the field that connects to parent, NULL otherwise.
   */
  public static function getFacilityParentFieldName(NodeInterface $node) : string | null {
    $parent = $node->hasField('field_office') ? 'field_office' : NULL;
    $system = $node->hasField('field_region_page') ? 'field_region_page' : NULL;
    return $system ?? $parent ?? NULL;
  }

}
