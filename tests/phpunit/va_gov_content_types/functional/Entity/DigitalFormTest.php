<?php

namespace tests\phpunit\va_gov_content_types\functional\Entity;

use Drupal\va_gov_content_types\Entity\DigitalForm;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the DigitalForm class.
 *
 * @group functional
 * @group all
 */
class DigitalFormTest extends VaGovExistingSiteBase {

  /**
   * Verify that `digital_form` nodes have this bundle class.
   */
  public function testBundleClass() {
    $digital_form_attrs = [
      'field_expiration_date' => '2025-08-28',
      'field_respondent_burden' => 5,
      'field_va_form_number' => '12345',
      'field_omb_number' => '1234-5678',
      'title' => 'Test Digital Form',
      'type' => 'digital_form',
    ];

    $node = $this->createNode($digital_form_attrs);
    $this->assertEquals(DigitalForm::class, get_class($node));
    $this->assertEquals($node->getTitle(), $digital_form_attrs['title']);
    $this->assertEquals(
      $node->get('field_va_form_number')->getString(),
      $digital_form_attrs['field_va_form_number']
    );
    $this->assertEquals(
      $node->get('field_expiration_date')->getString(),
      $digital_form_attrs['field_expiration_date']
    );
    $this->assertEquals(
      $node->get('field_omb_number')->getString(),
      $digital_form_attrs['field_omb_number']
    );
    $this->assertEquals(
      $node->get('field_respondent_burden')->getString(),
      $digital_form_attrs['field_respondent_burden']
    );
  }

}
