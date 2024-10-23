<?php

namespace tests\phpunit\va_gov_form_builder\unit\Plugin\Validation\Constraint;

use Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueField;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for the UniqueField constraint.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueField
 */
class UniqueFieldTest extends VaGovUnitTestBase {

  /**
   * Test the UniqueField constraint properties.
   */
  public function testUniqueFieldConstraint() {
    $constraint = new UniqueField();
    $this->assertEquals(
      'There is already a :bundle_label in the system with :field_label `:field_value`.',
      $constraint->message,
      'The default validation message should be correct.'
    );
  }

}
