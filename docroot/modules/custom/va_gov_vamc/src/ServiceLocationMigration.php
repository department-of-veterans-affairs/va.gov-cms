<?php

namespace Drupal\va_gov_vamc;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Psr\Log\LogLevel;

/**
 * Single use class to do migration from facility service node to service location.
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
   * @var \Drupal\node\Entity\NodeInterface
   */
  public $serviceLocation;

  public function __construct(array &$sandbox) {
    $cms_migrator_uid = 1317;
    $processed_nids = '';
    $batch_size = 25;
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $nids = array_slice($sandbox['items_to_process'], 0, $batch_size, TRUE);
    $facility_services = (empty($nids)) ? [] : $node_storage->loadMultiple($node_ids);

  foreach ($facility_services as $nid => $facility_service_node) {
    /** @var \Drupal\node\Entity\NodeInterface $facility_service_node */
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
    foreach ($service_locations as $service_location) {
      /** @var \Drupal\paragraphs\ParagraphInterface $service_location */
      $this->serviceLocation = $service_location;
      $this->migrate_address();
      $this->migrate_appointment_into_text();
      $this->migrate_appointment_phone_number();
      $this->migrate_contact_info();
      $this->migrate_hours();
      $this->migrate_schedule_online();
      $this->migrate_walkins_accepted();
      // Save the paragraph as a new revision.
      $service_location->setNewRevision(TRUE);
      // @todo I think I need to grab a new revision id for the node save.
      // put this into the node field'target_revision_id' => $service_location->getRevisionId(),
      //$service_location->set('field_fieldname1', 'some value');
      $service_location->save();

    }
    // Make this change a new revision.
    $facility_service_node->setNewRevision(TRUE);
    // Set revision author to uid 1317.
    $facility_service_node->setRevisionAuthorId($cms_migrator_uid);
    $facility_service_node->setChangedTime(time());
    $facility_service_node->setRevisionCreationTime(time());
    $facility_service_node->setOwnerId($cms_migrator_uid);
    // Set revision log message.
    $facility_service_node->setRevisionLogMessage('Automated move of text from intro_text to intro_text_limited_html');
    $facility_service_node->save();
// @todo Must also handle forward revisions.

    unset($sandbox['items_to_process'][$nid]);
    $i++;

    $processed_nids .= $nid . ', ';
    $sandbox['current']++;
  }
    // Log the processed nodes.
  Drupal::logger('va_gov_db')
    ->log(LogLevel::INFO, 'Facility Healht Service nodes %current nodes saved to migrate some data into paragraphs. Nodes processed: %nids', [
      '%current' => $sandbox['current'],
      '%nids' => $processed_nids,
    ]);

}


  protected function migrate_address(){
    // Moving from:
    // Moving to: service_location -> field_service_location_address

  }

  protected function migrate_appointment_into_text(){
    // Moving from:
    // Moving to: paragraph.service_location.field_appointment_intro_text

    // Needs a bifurcation for VAMC vs VBA.
  }

  protected function migrate_appointment_phone_number() {
    // Moving from: field_phone_numbers_paragraph ??
    // Moving to: field_phone (paragraphs)
    // and: field_use_main_facility_phone (boolean)
  }

  protected function migrate_contact_info() {
    // Moving from:
    // Moving to: service_location ->field_email_contacts.
  }

  protected function migrate_hours() {
    // Moving from:
    // Moving to: service_location ->field_office_hours (Office hours field).
    // and: service_location ->field_hours (list).
  }

  protected function migrate_schedule_online() {
    // Moving from: field_online_scheduling_availabl
    // Moving to:
    $schedule_online_map = [
      // schedule online => service location schedule online
      'yes' => 'yes',
      'no' => 'no',
      // This is the do no harm, option defaulting to most restrictive.
      // 'unspecified' => '??? @todo is this needed',
    ];

    return $schedule_online_map[$option];
  }

  protected function migrate_walkins_accepted() {
    // Moving from: 	node field_walk_ins_accepted
    // Moving to:
    $walkins_accepted_map = [
      // walkins accepted => office visits
      'yes' => 'yes with or without an appointment',
      'no' => 'yes by appointment only',
      // This is the do no harm, option defaulting to most restrictive.
      'unspecified' => 'yes by appointment only',
    ];
  }

}
