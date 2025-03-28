<?php

namespace Drupal\va_gov_facilities;

use Drupal\node\NodeInterface;

/**
 * Wrapper class of largely static helper functions related to Facilities.
 */
class FacilityOps {

  /**
   * Get facility types.
   *
   * @return array
   *   Array of facility types.
   */
  public static function getFacilityTypes() : array {
    return [
      'health_care_local_facility',
      'nca_facility',
      'vba_facility',
      'vet_center_cap',
      'vet_center_mobile_vet_center',
      'vet_center_outstation',
      'vet_center',
    ];
  }

  /**
   * Returns the section for the requested facility type.
   *
   * @param string $section
   *   Values: vamc, vet center, vba, nca.
   *
   * @return int
   *   The section id of the facility type, or 0.
   */
  public static function getFacilityTypeSectionId($section) {
    $sections = [
      'vamc' => 8,
      'vet center' => 190,
      'vba' => 191,
      'nca' => 192,
    ];

    return $sections[$section] ?? 0;
  }

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
    return in_array($node->bundle(), self::getFacilityTypes());
  }

  /**
   * Get facility types without status info.
   *
   * @return array
   *   Facility types that have no status.
   */
  public static function getFacilityTypesWithoutStatus() : array {
    return [
      'vet_center_mobile_vet_center',
    ];
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
    return in_array($node->bundle(), self::getFacilityTypesWithoutStatus());
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
   * Get facility types that have status info.
   *
   * @return array
   *   An array of facility types that have status.
   */
  public static function getFacilityTypesWithStatus() : array {
    return [
      'health_care_local_facility',
      'nca_facility',
      'vba_facility',
      'vet_center_cap',
      'vet_center_outstation',
      'vet_center',
    ];
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
    return in_array($type, self::getFacilityTypesWithStatus());
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
   * Checks if the entity is a facility node that should be auto-archived.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility to be auto-archived. FALSE otherwise.
   */
  public static function isAutoArchiveFacility(NodeInterface $node) : bool {
    return self::isBundleFacilityToAutoArchive($node->bundle());
  }

  /**
   * Checks if the entity is a facility to be auto-archived.
   *
   * @param string $type
   *   The bundle id to evaluate.
   *
   * @return bool
   *   TRUE if it is a facility bundle to be auto-archived. FALSE otherwise.
   */
  public static function isBundleFacilityToAutoArchive(string $type) : bool {
    $feature_status = \Drupal::service('feature_toggle.feature_status');
    // Get status of Vet Center Outstation enhancements.
    $status = $feature_status->getStatus('feature_vet_center_outstation_enhancements');
    // If status is true, Outstations cannot be autoarchived.
    if ($status) {
      $facilities_to_auto_archive = [
        'nca_facility',
        'vet_center_mobile_vet_center',
      ];
    }
    else {
      $facilities_to_auto_archive = [
        'nca_facility',
        'vet_center_mobile_vet_center',
        'vet_center_outstation',
      ];
    }

    return in_array($type, $facilities_to_auto_archive);
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
    $feature_status = \Drupal::service('feature_toggle.feature_status');
    // Get status of Vet Center Outstation enhancements.
    $status = $feature_status->getStatus('feature_vet_center_outstation_enhancements');
    // If status is true, Outstations have their own pages.
    if ($status) {
      $facilities_with_no_fe_page = [
        'vet_center_cap',
        'vet_center_mobile_vet_center',
      ];
    }
    else {
      $facilities_with_no_fe_page = [
        'vet_center_cap',
        'vet_center_mobile_vet_center',
        'vet_center_outstation',
      ];
    }

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
