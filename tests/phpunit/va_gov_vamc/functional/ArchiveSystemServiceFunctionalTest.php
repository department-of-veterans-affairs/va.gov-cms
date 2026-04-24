<?php

namespace tests\phpunit\va_gov_vamc\functional;

use Drupal\node\Entity\Node;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Test for archiving a system Hhalth service and its related facility services.
 *
 * @group va_gov_vamc
 * @group functional
 */
class ArchiveSystemServiceFunctionalTest extends VaGovExistingSiteBase {

  /**
   * Minimal test to check reference field persistence.
   */
  public function testFacilityIsArchivedWithSystem() {
    // Create referenced system node.
    $system_service = $this->createNode([
      'type' => 'regional_health_care_service_des',
      'title' => 'System Service',
      'moderation_state' => 'published',
      'status' => 1,
    ]);
    $system_service->save();

    // Create facility node with reference field set.
    $facility_service = $this->createNode([
      'type' => 'health_care_local_health_service',
      'title' => 'Facility Service',
      'field_regional_health_service' => [
        ['target_id' => $system_service->id()],
      ],
      'moderation_state' => 'published',
      'status' => 1,
    ]);
    $facility_service->save();

    // Archive system.
    $reloaded_system = Node::load($system_service->id());
    $reloaded_system->set('moderation_state', 'archived');
    $reloaded_system->save();

    // Reload and check field again.
    $reloaded_facility = Node::load($facility_service->id());

    // Assert field is still set.
    $this->assertNotEmpty(
      $reloaded_facility->get('field_regional_health_service')->getValue(),
      'Reference field should persist after update.'
    );
    // Assert system is archived.
    $this->assertTrue(
      $reloaded_system->get('moderation_state')->value === 'archived',
      'System service is archived.'
    );
    // Assert facility is also archived.
    $this->assertTrue(
      $reloaded_facility->get('moderation_state')->value === 'archived',
      'Facility service is also archived.'
    );
  }

}
