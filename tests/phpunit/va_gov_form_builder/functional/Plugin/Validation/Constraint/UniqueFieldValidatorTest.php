<?php

namespace tests\phpunit\va_gov_form_builder\functional\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueField;
use tests\phpunit\va_gov_form_builder\Traits\SharedConstants;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the UniqueFieldValidator constraint validator.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueFieldValidator
 */
class UniqueFieldValidatorTest extends VaGovExistingSiteBase {

  use SharedConstants;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Validation violations.
   *
   * @var \Symfony\Component\Validator\ConstraintViolationInterface
   */
  private $violations;

  /**
   * Deletes all entities that have our unique VA form number.
   *
   * These shouldn't exist, but might be left over from other test runs
   * if not cleaned up properly.
   */
  private function deleteLeftoverTestEntities() {
    $nodeStorage = $this->entityTypeManager->getStorage('node');

    $query = $nodeStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'digital_form')
      ->condition('field_va_form_number', self::getUniqueVaFormNumber());
    $nids = $query->execute();

    // Load and delete each node.
    if (!empty($nids)) {
      $nodes = $nodeStorage->loadMultiple($nids);
      foreach ($nodes as $node) {
        $node->delete();
      }
    }
  }

  /**
   * Returns a Digital Form node that has isNew() true.
   *
   * @param string $formNumber
   *   The VA form number to apply to the Digital Form.
   *
   * @return \Drupal\node\Entity\Node
   *   A Digital Form node
   */
  private static function createNewDigitalForm($formNumber = NULL) {
    // Calling Node::create does not save the entity to the database,
    // so the returned entity is a new node.
    return Node::create([
      'type' => 'digital_form',
      'field_va_form_number' => $formNumber ?? self::getUniqueVaFormNumber(),
    ]);
  }

  /**
   * Returns a Digital Form node that has isNew() false.
   *
   * @param string $formNumber
   *   The VA form number to apply to the Digital Form.
   *
   * @return \Drupal\node\Entity\Node
   *   A Digital Form node
   */
  private function createExistingDigitalForm($formNumber = NULL) {
    // Calling $this->createNode saves the entity to the database,
    // so the returned value is an existing node.
    return $this->createNode([
      'type' => 'digital_form',
      'field_va_form_number' => $formNumber ?? self::getUniqueVaFormNumber(),
    ]);
  }

  /**
   * Determines if there is a violation of the UniqueField constraint.
   */
  private function hasUniqueFieldViolation() {
    $uniqueFieldViolation = FALSE;
    foreach ($this->violations as $violation) {
      if ($violation->getConstraint() instanceof UniqueField) {
        $uniqueFieldViolation = TRUE;
      }
    }
    return $uniqueFieldViolation;
  }

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->entityTypeManager = \Drupal::entityTypeManager();
    $this->deleteLeftoverTestEntities();
  }

  /**
   * Tests the validator on a new entity that is unique.
   *
   * @covers ::validate
   */
  public function testValidateNewUniqueEntity() {
    $newDigitalForm = self::createNewDigitalForm();

    // A Digital Form node with our unique VA form number
    // should not already exist. This should not trigger
    // a violation.
    $this->violations = $newDigitalForm->validate();
    $this->assertFalse($this->hasUniqueFieldViolation());
  }

  /**
   * Tests the validator on a new entity that is not unique.
   *
   * @covers ::validate
   */
  public function testValidateNewNonUniqueEntity() {
    // First, create an existing unique entity.
    $this->createExistingDigitalForm();

    // Now create a new entity with the same VA form number.
    // This should trigger a validation violation.
    $newDigitalForm = self::createNewDigitalForm();
    $this->violations = $newDigitalForm->validate();
    $this->assertTrue($this->hasUniqueFieldViolation());
  }

  /**
   * Tests the validator on an existing entity that is unique.
   *
   * @covers ::validate
   */
  public function testValidateExistingUniqueEntity() {
    $existingDigitalForm = $this->createExistingDigitalForm();

    // A Digital Form node with our unique VA form number
    // should not already exist (other than this node).
    // This should not trigger a violation.
    $this->violations = $existingDigitalForm->validate();
    $this->assertFalse($this->hasUniqueFieldViolation());
  }

  /**
   * Tests the validator on an existing entity that is not unique.
   *
   * @covers ::validate
   */
  public function testValidateExistingNonUniqueEntity() {
    // First, create an existing unique entity.
    $this->createExistingDigitalForm();

    // Now create another existing entity with the same VA form number.
    // This should trigger a validation violation.
    $existingDigitalForm = $this->createExistingDigitalForm();
    $this->violations = $existingDigitalForm->validate();
    $this->assertTrue($this->hasUniqueFieldViolation());
  }

}
