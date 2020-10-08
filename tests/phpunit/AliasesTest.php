<?php

namespace tests\phpunit;

use Drupal\taxonomy\Entity\Vocabulary;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm correct alias settings.
 */
class Aliases extends ExistingSiteBase {

  /**
   * A test method to test VAMC System & Facility Health Services Aliases.
   *
   * @group aliases
   * @group all
   */
  public function testVAMCFacilityHealthServiceAlias() {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();

    // Create a VAMC System node.
    $system_node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $system_node->setPublished()->save();
    $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $system_node->id());
    $this->assertEquals('/va-test-health-care', $url_alias);

    // Create a VAMC facility location node.
    $facility_node = $this->createNode([
      'title' => 'Test VA Medical Center',
      'type' => 'health_care_local_facility',
      'field_region_page' => $system_node->id(),
      'uid' => $author->id(),
    ]);
    $facility_node->setPublished()->save();
    $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $facility_node->id());
    $this->assertEquals('/va-test-health-care/locations/test-va-medical-center', $url_alias);

    // Create a VHA health care service category term.
    $vha_service_vocab = Vocabulary::load('health_care_service_taxonomy');
    $vha_service_category_term = $this->createTerm($vha_service_vocab, [
      'name' => 'Test Services',
      'description' => 'Test Services Description',
      'uid' => $author->id(),
    ]);
    $vha_service_category_term->save();

    // Create a VHA health care service term.
    $vha_service_term = $this->createTerm($vha_service_vocab, [
      'name' => 'Test Service',
      'description' => 'Test Service Description',
      // Set parent to "Test Services".
      'parent' => $vha_service_category_term->id(),
      'uid' => $author->id(),
    ]);
    $vha_service_term->save();

    // Create a VAMC regional health care service node.
    $service_node = $this->createNode([
      'title' => 'Test Regional Service',
      'type' => 'regional_health_care_service_des',
      'field_region_page' => $system_node->id(),
      'field_service_name_and_descripti' => $vha_service_term->id(),
      'uid' => $author->id(),
    ]);
    $service_node->setPublished()->save();

    // Check the auto-generated title.
    $this->assertEquals('Test Service at VA Test health care', $service_node->getTitle());

    // Check the auto-generated URL alias.
    $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $service_node->id());
    $this->assertEquals('/va-test-health-care/health-services/test-service-at-va-test-health-care', $url_alias);

    // Create a VAMC facility local health care service node.
    $local_service_node = $this->createNode([
      'title' => 'Test Service at Test VA Medical Center',
      'type' => 'health_care_local_health_service',
      'field_facility_location' => $facility_node->id(),
      'field_regional_health_service' => $service_node->id(),
      'uid' => $author->id(),
    ]);
    $local_service_node->setPublished()->save();

    // Check the auto-generated title.
    $this->assertEquals('Test Service - Test VA Medical Center', $local_service_node->getTitle());

    // Assert that the path follows the pattern [node:field_facility_location:entity:url:path]/[node:field_regional_health_service:entity:field_service_name_and_descripti]
    $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $local_service_node->id());
    $this->assertEquals('/va-test-health-care/locations/test-va-medical-center/test-service', $url_alias);
  }

}
