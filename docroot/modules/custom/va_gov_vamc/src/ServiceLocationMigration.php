<?php

namespace Drupal\va_gov_vamc;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
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
   * @var \Drupal\paragraphs\ParagraphInterface
   */
  public $facilityService;

  /**
   * The current service_location paragraph being processed.
   *
   * @var \Drupal\node\NodeInterface
   */
  public $serviceLocation;

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
      }

      $this->migrateServicesLocationsFromFacility($service_locations);
      $message = 'Automated move of Facility Health Service data into Service Locations.';
      // Must grab this before save, because after save it will always be true.
      $is_latest_revision = $this->facilityService->isLatestRevision();
      save_node_revision($facility_service_node, $message, TRUE);

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
    Drupal::logger('va_gov_db')
      ->log(LogLevel::INFO, 'Facility Health Service nodes %current nodes saved to migrate some data into paragraphs. %forward_revisions were also updated. Nodes processed: %nids', [
        '%current' => $sandbox['current'],
        '%nids' => $processed_nids,
        '%forward_revisions' => $sandbox['forward_revisions_count'],
      ]);
  }

  protected function migrateServicesLocationsFromFacility(array $service_locations) {
    foreach ($service_locations as $service_location) {
      /** @var \Drupal\paragraphs\ParagraphInterface $service_location */
      $this->serviceLocation = $service_location;
      $this->migrateAddress();
      $this->migrateAppointmentIntoText();
      $this->migrateAppointmentPhoneNumber();
      $this->migrateContactInfo();
      $this->migrateHours();
      $this->migrateScheduleOnline();
      $this->migrateWalkinsAccepted();
      // Save the paragraph as a new revision.
      $service_location->setNewRevision(TRUE);
      // @todo I think I need to grab a new revision id for the node save.
      // put this into the node field'target_revision_id' => $service_location->getRevisionId(),
      // $service_location->set('field_fieldname1', 'some value');
//PREVENT THIS FROM SAVING TEMP
exit('BOOGA');
      $service_location->save();
    }
  }

  protected function migrateAddress() {
    // Moving from:
    // Moving to: service_location -> field_service_location_address.

    // @todo Double check this, I think this does not need to migrate.

  }

  protected function migrateAppointmentIntoText() {
    // Moving from:
    // Moving to: paragraph.service_location.field_appointment_intro_text
    // Needs a bifurcation for VAMC vs VBA.
  }

  protected function migrateAppointmentPhoneNumber() {
    // Moving from: field_phone_numbers_paragraph ??
    // Moving to: field_phone (paragraphs)
    // and: field_use_main_facility_phone (boolean)
  }

  protected function migrateContactInfo() {
    // Moving from:
    // Moving to: service_location ->field_email_contacts.
  }

  protected function migrateHours() {
    // Moving from:
    // Moving to: service_location ->field_office_hours (Office hours field).
    // and: service_location ->field_hours (list).
    //$this->serviceLocation->set('field_office_hours') = $this->facilityService->get('field_office_hours');
  }

  protected function migrateScheduleOnline() {
    // Moving from: field_online_scheduling_availabl.
    // Moving to:
    $schedule_online_map = [
      // Schedule online => service location schedule online.
      'yes' => 'yes',
      'no' => 'no',
      // This is the do no harm, option defaulting to most restrictive.
      // 'unspecified' => '??? @todo is this needed',
    ];
    $lookup = $this->facilityService->get('field_online_scheduling_availabl')->value;
    $new_value = script_libary_map_to_value($lookup, $schedule_online_map);
  }

  protected function migrateWalkinsAccepted() {
    // Moving from: node field_walk_ins_accepted.
    // Moving to:
    $walkins_accepted_map = [
      // Walkins accepted => office visits.
      'yes' => 'yes with or without an appointment',
      'no' => 'yes by appointment only',
      // This is the do no harm, option defaulting to most restrictive.
      'unspecified' => 'yes by appointment only',
    ];
    $lookup = $this->facilityService->get('field_walk_ins_accepted')->value;
    $new_value = script_libary_map_to_value($lookup, $walkins_accepted_map);
  }

}
