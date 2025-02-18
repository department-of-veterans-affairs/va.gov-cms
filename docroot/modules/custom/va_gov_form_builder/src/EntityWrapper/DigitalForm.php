<?php

namespace Drupal\va_gov_form_builder\EntityWrapper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * A wrapper class around Digital Form nodes.
 *
 * @method int id()
 * @method string getTitle()
 * @method \Drupal\Core\Field\FieldItemListInterface get(string $field_name)
 * @method \Symfony\Component\Validator\ConstraintViolationListInterface validate()
 * @method int save() Saves the entity and returns the save status
 * @method \Drupal\node\NodeInterface set(string $field_name, mixed $value, bool $notify = TRUE) Sets a field value
 *
 * The following line is included because the public method `addStep`
 * makes dynamic calls to otherwise uncalled private methods, and those
 * private methods trigger unused-method warnings when they are, in fact,
 * not unused:
 * @phpcs:disable DrupalPractice.Objects.UnusedPrivateMethod.UnusedMethod
 */
class DigitalForm {
  /**
   * The standard steps of a Digital Form.
   */
  const STANDARD_STEPS = [
    'your_personal_info' => 'digital_form_your_personal_info',
    'address_info' => 'digital_form_address',
    'contact_info' => 'digital_form_phone_and_email',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Digital Form node.
   *
   * @var \Drupal\node\NodeInterface
   */
  private $node;

  /**
   * Constructs a DigitalForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\node\NodeInterface $node
   *   The Digital Form node to wrap.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, NodeInterface $node) {
    $this->entityTypeManager = $entity_type_manager;

    if ($node->getType() !== 'digital_form') {
      throw new \InvalidArgumentException('The node must be of type "digital_form".');
    }

    $this->node = $node;
  }

  /**
   * Magic method to forward other method calls to the node.
   *
   * This makes it so we can call, for example, $wrappedNode->getTitle().
   * These methods are annotated in the class comment above.
   *
   * @param string $name
   *   The name of the method being called.
   * @param array $arguments
   *   The arguments passed to the method.
   *
   * @return mixed
   *   The return value of the method called on the node.
   */
  public function __call($name, $arguments) {
    if (method_exists($this->node, $name)) {
      return call_user_func_array([$this->node, $name], $arguments);
    }

    throw new \BadMethodCallException("Method $name does not exist on the underlying node class.");
  }

  /**
   * Returns an array of all steps added to the form.
   *
   * @return array
   *   A collection of all steps.
   */
  public function getAllSteps() {
    $steps = [];

    if ($this->node->hasField('field_chapters')) {
      $chapters = $this->node->get('field_chapters')->getValue();

      foreach ($chapters as $chapter) {
        $paragraph = $this->entityTypeManager
          ->getStorage('paragraph')
          ->load($chapter['target_id']);

        if ($paragraph) {
          $steps[] = [
            'type' => $paragraph->bundle(),
            'fields' => array_map(function ($field) {
              return $field->getValue();
            }, $paragraph->getFields()),
          ];
        }
      }
    }

    return $steps;
  }

  /**
   * Returns an array of all steps that are not standard steps.
   *
   * @return array
   *   A collection of non-standard steps.
   */
  public function getNonStandarddSteps() {
    $allSteps = $this->getAllSteps();
    $nonStandardSteps = array_values(array_filter($allSteps, function ($step) {
       return !in_array($step['type'], array_values(self::STANDARD_STEPS));
    }));

    return $nonStandardSteps;
  }

  /**
   * Determines if the Digital Form node has a chapter of a given type.
   *
   * If the node has a chapter (paragraph) of the given type, returns TRUE.
   * Otherwise, returns FALSE.
   *
   * @param string $type
   *   The chapter (paragraph) type.
   *
   * @return bool
   *   TRUE if the chapter exists; FALSE if the chapter
   *   does not exist.
   */
  public function hasChapterOfType($type) {
    if ($this->node->hasField('field_chapters') && !$this->node->get('field_chapters')->isEmpty()) {
      $chapters = $this->node->get('field_chapters')->getValue();

      foreach ($chapters as $chapter) {
        if (isset($chapter['target_id'])) {
          $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($chapter['target_id']);

          if ($paragraph) {
            if ($paragraph->bundle() === $type) {
              return TRUE;
            }
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * Returns the status of a step on the Digital Form node.
   *
   * Completeness of the step varies by step, and is documented
   * in the function body.
   *
   * @param string $stepName
   *   The step name of the step in question.
   *
   * @return 'complete'|'incomplete'
   *   Returns 'complete' if step is complete.
   *   Returns 'incomplete if step is incomplete
   *   or if the step name does not exist.
   */
  public function getStepStatus($stepName) {
    if ($stepName === 'form_info') {
      // If the node exists, this will necessarily be complete.
      return 'complete';
    }

    if ($stepName === 'review_and_sign') {
      // This is added automatically by the Forms Library.
      return 'complete';
    }

    if (in_array($stepName, [
      'intro',
      'confirmation',
    ])) {
      // These haven't been handled yet.
      // Return 'incomplete' for the time being.
      return 'incomplete';
    }

    // Standard steps are complete if a corresponding chapter exists.
    if (array_key_exists($stepName, self::STANDARD_STEPS)) {
      $paragraphName = self::STANDARD_STEPS[$stepName];
      return $this->hasChapterOfType($paragraphName)
        ? 'complete'
        : 'incomplete';
    }

    return 'incomplete';
  }

  /**
   * Adds a Your-personal-information step to the Digital Form.
   *
   * @param mixed $fields
   *   The field values to add. If needed values are not present,
   *   they take defaults as defined in the code.
   */
  private function addYourPersonalInfoStep($fields = NULL) {
    if (!$this->node) {
      throw new \Exception('Digital Form is not set. Cannot add steps to an empty Digital Form object.');
    }

    $nameAndDob = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'digital_form_name_and_date_of_bi',
      'field_title' => $fields['field_name_and_date_of_birth']['field_title'] ?? 'Name and date of birth',
      'field_include_date_of_birth' => $fields['field_name_and_date_of_birth']['field_include_date_of_birth'] ?? TRUE,
    ]);

    $identificationInfo = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'digital_form_identification_info',
      'field_title' => $fields['field_identification_information']['field_title'] ?? 'Identification information',
      'field_include_veteran_s_service' => $fields['field_identification_information']['field_include_veteran_s_service'] ?? FALSE,
    ]);

    $yourPersonalInformation = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'digital_form_your_personal_info',
      'field_name_and_date_of_birth' => $nameAndDob,
      'field_identification_information' => $identificationInfo,
    ]);

    $this->node->get('field_chapters')->appendItem($yourPersonalInformation);
  }

  /**
   * Adds an Address-information step to the Digital Form.
   *
   * @param mixed $fields
   *   The field values to add. If needed values are not present,
   *   they take defaults as defined in the code.
   */
  private function addAddressInfoStep($fields = NULL) {
    if (!$this->node) {
      throw new \Exception('Digital Form is not set. Cannot add steps to an empty Digital Form object.');
    }

    $addressInformation = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'digital_form_address',
      'field_title' => $fields['field_title'] ?? 'Mailing address',
      'field_military_address_checkbox' => $fields['field_military_address_checkbox'] ?? TRUE,
    ]);

    $this->node->get('field_chapters')->appendItem($addressInformation);
  }

  /**
   * Adds a Contact-information step to the Digital Form.
   *
   * @param mixed $fields
   *   The field values to add. If needed values are not present,
   *   they take defaults as defined in the code.
   */
  private function addContactInfoStep($fields = NULL) {
    if (!$this->node) {
      throw new \Exception('Digital Form is not set. Cannot add steps to an empty Digital Form object.');
    }

    $contactInformation = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'digital_form_phone_and_email',
      'field_title' => $fields['field_title'] ?? 'Phone and email address',
      'field_include_email' => $fields['field_include_email'] ?? TRUE,
    ]);

    $this->node->get('field_chapters')->appendItem($contactInformation);
  }

  /**
   * Adds a step to the Digital Form.
   *
   * @param string $stepName
   *   The name of the step to add.
   * @param array<string,mixed> $fields
   *   The field values. If not passed, defaults
   *   are used in the underlying calls.
   */
  public function addStep($stepName, $fields = NULL) {
    if (!$this->node) {
      throw new \Exception('Digital Form is not set. Cannot add steps to an empty Digital Form object.');
    }

    // Ex: 'your_personal_info' => 'addYourPersonalInfoStep'.
    $methodName = 'add' . str_replace('_', '', ucwords($stepName, '_')) . 'Step';

    if (method_exists($this, $methodName)) {
      return $this->$methodName($fields);
    }

    throw new \InvalidArgumentException("Method $methodName does not exist.");
  }

}
