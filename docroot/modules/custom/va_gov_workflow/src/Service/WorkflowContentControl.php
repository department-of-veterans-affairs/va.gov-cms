<?php

namespace Drupal\va_gov_workflow\Service;

use Drupal\va_gov_user\Service\UserPermsService;

/**
 * Service used for controlling entity workflow transitions.
 */
class WorkflowContentControl {

  /**
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   */
  public function __construct(
    UserPermsService $user_perms_service
  ) {
    $this->userPermsService = $user_perms_service;
  }

  /**
   * An array of node bundles that only Admins can archive.
   *
   * @return array
   *   The content type array.
   */
  public function getBundlesArchiveableByAdmins() {
    return [
      'basic_landing_page',
      'centralized_content',
      'documentation_page',
      'event_listing',
      'health_care_local_facility',
      'health_care_region_page',
      'health_services_listing',
      'landing_page',
      'leadership_listing',
      'locations_listing',
      'nca_facility',
      'office',
      'page',
      'press_releases_listing',
      'publication_listing',
      'story_listing',
      'support_service',
      'va_form',
      'vamc_operating_status_and_alerts',
      'vamc_system_policies_page',
      'vamc_system_register_for_care',
      'vamc_system_medical_records_offi',
      'vamc_system_billing_insurance',
      'vba_facility',
      'vet_center',
      'vet_center_locations_list',
    ];
  }

  /**
   * Check if subject node bundle is allowed to be archived by non-admin user.
   *
   * @param string $bundle
   *   The entity bundle type.
   *
   * @return bool
   *   Returns true if non-admin can archive, false if non-admin can not.
   */
  public function isBundleArchiveableByNonAdmins($bundle) {
    return in_array($bundle, $this->getBundlesArchiveableByAdmins()) ? FALSE : TRUE;
  }

}
