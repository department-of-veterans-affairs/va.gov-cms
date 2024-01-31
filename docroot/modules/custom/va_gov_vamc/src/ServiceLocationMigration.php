<?php

namespace Drupal\va_gov_vamc;

use Drupal\paragraphs\Entity\Paragraph;
use Psr\Log\LogLevel;

/**
 * Single use class to migrate from facility service node to service location.
 *
 * This is not a service, it is only needed one time so DI can not be used.
 */
class ServiceLocationMigration {


  /**
   * The current facility service node being processed.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $facilityService;

  /**
   * The current service_location paragraph being processed.
   *
   * @var \Drupal\paragraphs\ParagraphInterface
   */
  protected $serviceLocation;

  /**
   * Constructor for ServiceLocationMigration.
   *
   * @param array $sandbox
   *   Sandbox variable for keeping state during batches.
   */
  public function __construct(array &$sandbox) {
    require_once __DIR__ . '/../../../../../scripts/content/script-library.php';
    $processed_nids = '';
    $batch_size = 25;
    $node_storage = get_node_storage();
    $nids = array_slice($sandbox['items_to_process'], 0, $batch_size, TRUE);
    $facility_services = (empty($nids)) ? [] : $node_storage->loadMultiple(array_values($nids));
    foreach ($facility_services as $nid => $facility_service_node) {
      /** @var \Drupal\node\NodeInterface $facility_service_node */
      $this->facilityService = $facility_service_node;
      // Gather existing service locations.
      $service_locations = $facility_service_node->get('field_service_location')->referencedEntities();
      if (empty($service_locations)) {
        // There are no service locations, but we still need to put the old node
        // data some place, so we need a new service location.
        $new_service_location = Paragraph::create(['type' => 'field_service_location']);
        $facility_service_node->field_service_location->appendItem($new_service_location);
        $service_locations = [$new_service_location];
        $this->facilityService->field_service_location[] = $new_service_location;
        $sandbox['service_locations_created_count'] = (isset($sandbox['service_locations_created_count'])) ? ++$sandbox['service_locations_created_count'] : 1;
      }
      else {
        $sandbox['service_locations_updated_count'] = (isset($sandbox['service_locations_updated_count'])) ? ++$sandbox['service_locations_updated_count'] : 1;
      }

      $this->migrateServicesLocationsFromFacility($service_locations);
      $message = 'Automated move of Facility Health Service data into Service Locations.';
      // Must grab this before save, because after save it will always be true.
      $is_latest_revision = $this->facilityService->isLatestRevision();
      save_node_revision($this->facilityService, $message, TRUE);

      if (!$is_latest_revision) {
        // The Facility service we have is the default revision, but if it is
        // not the latest, there is a forward revision we need to update too.
        $forward_revision = get_node_at_latest_revision($nid);
        $this->facilityService = $forward_revision;
        $this->migrateServicesLocationsFromFacility($service_locations);
        save_node_revision($facility_service_node, $message, TRUE);
        $sandbox['forward_revisions_count'] = (isset($sandbox['forward_revisions_count'])) ? ++$sandbox['forward_revisions_count'] : 1;
      }

      unset($sandbox['items_to_process'][_va_gov_stringifynid($nid)]);
      $processed_nids .= $nid . ', ';
      $sandbox['current']++;
    }
    // Log the processed nodes.
    \Drupal::logger('va_gov_vamc')
      ->log(LogLevel::INFO, 'Facility Health Service nodes %current nodes saved to migrate some data into paragraphs. %forward_revisions were also updated. Nodes processed: %nids', [
        '%current' => $sandbox['current'],
        '%nids' => $processed_nids,
        '%forward_revisions' => $sandbox['forward_revisions_count'] ?? 0,
      ]);
  }

  /**
   * Migrate into each of the service location fields.
   *
   * @param array $service_locations
   *   An array of service locations to migrate into.
   */
  protected function migrateServicesLocationsFromFacility(array $service_locations): void {
    foreach ($service_locations as $service_location) {
      /** @var \Drupal\paragraphs\ParagraphInterface $service_location */
      $this->serviceLocation = $service_location;
      $this->migrateAppointmentIntroType();
      $this->migrateAppointmentIntroText();
      $this->migrateAppointmentUseFacilityPhone();
      $this->migrateAppointmentPhoneNumbers();
      $this->migrateScheduleOnline();
      $this->migrateWalkinsAccepted();
      $this->serviceLocation->setNewRevision(TRUE);
    }
  }

  /**
   * Move appointment type from the service node to the service location.
   */
  protected function migrateAppointmentIntroType(): void {
    // Moving from: field_hservice_appt_intro_select.
    // Moving to: paragraph.service_location.field_appt_intro_text_type.
    $intro_type = $this->facilityService->get('field_hservice_appt_intro_select')->value;
    $type_map = [
      'default_intro_text' => 'use_default_text',
      'custom_intro_text' => 'customize_text',
      'no_intro_text' => 'remove_text',
      'default' => 'use_default_text',
    ];
    $new_value = script_libary_map_to_value($intro_type, $type_map);
    $this->serviceLocation->set('field_appt_intro_text_type', $new_value);
  }

  /**
   * Move the appointment text from the service node to the service location.
   */
  protected function migrateAppointmentIntroText(): void {
    // Moving from: field_hservice_appt_leadin
    // Moving to: paragraph.service_location.field_appointment_intro_text
    // Needs a bifurcation for VAMC vs VBA.
    $intro_text = $this->facilityService->get('field_hservice_appt_leadin')->value;
    $this->serviceLocation->set('field_appt_intro_text_custom', $intro_text);
  }

  /**
   * Populates the service location based on the presence of apt phone.
   */
  protected function migrateAppointmentUseFacilityPhone(): void {
    // Based on the presence of data in field_phone_numbers_paragraph.
    // To  field_use_facility_phone_number.
    $has_no_apt_phone = $this->facilityService->get('field_phone_numbers_paragraph')->isEmpty();
    // The assumption is that if there is no existing phone, then it should
    // be the main number by default.  There is a risk in that they wanted
    // the main number and to provide a second, but there is nothing to
    // base that decision on to make it cleaner.
    $use_main_facility_phone = ($has_no_apt_phone) ? 1 : 0;
    $this->serviceLocation->set('field_use_facility_phone_number', $use_main_facility_phone);
  }

  /**
   * Move the apt phone numbers properties.
   */
  protected function migrateAppointmentPhoneNumbers(): void {
    // Moving from: field_phone_numbers_paragraph.
    // Moving to: field_other_phone_numbers (paragraphs).
    $phone_paragraphs = $this->facilityService->get('field_phone_numbers_paragraph')->referencedEntities();

    foreach ($phone_paragraphs as $phone) {
      // Add each one to the service location.
      // Need to move Type, Phone number, Extension number, and Label.
      $data = [
        'type' => 'phone_number',
        'field_phone_number_type' => $phone->get('field_phone_number_type')->value,
        'field_phone_number' => $phone->get('field_phone_number')->value,
        'field_phone_extension' => $phone->get('field_phone_extension')->value,
        'field_phone_label' => $phone->get('field_phone_label')->value,
      ];
      $new_phone = Paragraph::create($data);
      $this->serviceLocation->field_other_phone_numbers[] = $new_phone;
    }
  }

  /**
   * Moves the online scheduling from Service to Service location.
   */
  protected function migrateScheduleOnline(): void {
    // Moving from: field_online_scheduling_availabl.
    // Moving to: field_online_scheduling_avail.
    $schedule_online_map = [
      // Schedule online => service location schedule online.
      // 'Yes' => 'Yes'.
      '1' => 'yes',
      // 'No' => 'No'.
      '0' => 'no',
      // This is the do no harm, option defaulting to most restrictive.
      // 'unspecified' => 'No'.
      'not_applicable' => 'no',
      'default' => 'no',
    ];
    $lookup = $this->facilityService->get('field_online_scheduling_availabl')->value;
    $new_value = script_libary_map_to_value($lookup, $schedule_online_map);
    $this->serviceLocation->set('field_online_scheduling_avail', $new_value);
  }

  /**
   * Move and map the walkins accepted to office visits.
   */
  protected function migrateWalkinsAccepted(): void {
    // Moving from: node field_walk_ins_accepted.
    // Moving to: field_office_visits.
    $walkins_accepted_map = [
      // Walkins accepted => office visits.
      // 'No' => 'yes by appointment only'.
      '0' => 'no',
      // 'Yes' => 'Yes, with or without an appointment'.
      '1' => 'yes_with_or_without_appointment',
      '' => '',
      // This is the do no harm, option defaulting to most restrictive.
      // 'unspecified' => 'yes by appointment only'.
      'not_applicable' => 'yes_appointment_only',
      'default' => 'yes_appointment_only',
    ];
    $lookup = $this->facilityService->get('field_walk_ins_accepted')->value;
    $new_value = script_libary_map_to_value($lookup, $walkins_accepted_map);
    $this->serviceLocation->set('field_office_visits', $new_value);
  }

}
