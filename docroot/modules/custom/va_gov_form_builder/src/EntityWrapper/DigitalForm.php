<?php

namespace Drupal\va_gov_form_builder\EntityWrapper;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityConstraintViolationList;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\va_gov_form_builder\Traits\EntityReferenceRevisionsOperations;

/**
 * A wrapper class around Digital Form nodes.
 *
 * @method int id()
 * @method string getTitle()
 * @method \Drupal\Core\Field\FieldItemListInterface get(string $field_name)
 * @method \Symfony\Component\Validator\ConstraintViolationListInterface validate()
 * @method int save() Saves the entity and returns the save status
 * @method NodeInterface set(string $field_name, mixed $value, bool $notify = TRUE) Sets a field value
 *
 * The following line is included because the public method `addStep`
 * makes dynamic calls to otherwise uncalled private methods, and those
 * private methods trigger unused-method warnings when they are, in fact,
 * used:
 * @phpcs:disable DrupalPractice.Objects.UnusedPrivateMethod.UnusedMethod
 */
class DigitalForm {

  use EntityReferenceRevisionsOperations;
  use StringTranslationTrait;

  /**
   * The standard steps of a Digital Form.
   */
  const STANDARD_STEPS = [
    'your_personal_info' => 'digital_form_your_personal_info',
    'address_info' => 'digital_form_address',
    'contact_info' => 'digital_form_phone_and_email',
  ];

  /**
   * Sort and delete actions available for steps.
   *
   * Any updates to these actions will need to also be made to the
   * va_gov_form_builder.step_action route access configuration.
   */
  const STEP_ACTIONS = [
    'moveup' => 'moveup',
    'movedown' => 'movedown',
    'delete' => 'delete',
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
            'paragraph' => $paragraph,
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
   * @param null|\Drupal\paragraphs\ParagraphInterface $paragraph
   *   Optional paragraph entity for this step.
   *
   * @return string
   *   Returns 'complete' if step is complete. Returns 'incomplete' if step is
   *   incomplete or if the step name does not exist.
   */
  public function getStepStatus(string $stepName, null|ParagraphInterface $paragraph = NULL) {
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

    // Use recursive entity validation to determine custom step status. Any
    // violation in the custom step paragraph or descendent paragraphs will
    // cause the status to be incomplete. This method is used to reduce
    // technical debt if the custom step content model, or the content model of
    // its children, is updated. This could also support future enhancements to
    // display _why_ the step is not complete.
    if ($paragraph instanceof ParagraphInterface && $stepName === 'custom') {
      $violations = $this->recursiveEntityReferenceRevisionValidator($paragraph, new EntityConstraintViolationList($paragraph));
      return $violations->count() > 0 ? 'incomplete' : 'complete';
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

  /**
   * Execute the given action on a step.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *    The step paragraph we are acting upon.
   * @param string $action
   *    The action to take.
   *
   * @throws \Exception
   * @throws \InvalidArgumentException
   */
  public function executeStepAction(ParagraphInterface $paragraph, string $action): void {
    if (!$this->node) {
      throw new \Exception('Digital Form is not set. Step cannot take an action on an empty Digital Form object.');
    }
    if (in_array($action, DigitalForm::STEP_ACTIONS)) {
      match($action) {
        self::STEP_ACTIONS['moveup'], self::STEP_ACTIONS['movedown'] => $this->sortStep($paragraph, $action),
        self::STEP_ACTIONS['delete'] => $this->deleteStep($paragraph),
      };
    }
    else {
      throw new \InvalidArgumentException($this->t('Invalid step sort action'));
    }
  }

  /**
   * Sort a step moving in the direction the $direction calls for.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The step paragraph being sorted.
   * @param string $direction
   *   The direction to sort. Initially either 'moveup' or 'movedown'.
   *
   * @throws \Exception
   * @throws \InvalidArgumentException
   */
  public function sortStep(ParagraphInterface $paragraph, string $direction): void {
    if (!($direction === self::STEP_ACTIONS['moveup']) && !($direction === self::STEP_ACTIONS['movedown'])) {
      throw new \InvalidArgumentException($this->t('Invalid step sort action'));
    }
    if (!$this->node) {
      throw new \Exception('Digital Form is not set. Cannot sort steps on an empty Digital Form object.');
    }

    // Moving an item presents some challenges:
    // - Standard steps may or may not be between custom steps. They can appear anywhere in the field.
    // - The field ItemList parent class has a protected rekey() method that is
    //   called internally only when addItem() or removeItem() are called. This
    //   method sets proper context on the field and re-keys indexes. We need to
    //   ensure this gets called.
    // - There is no builtin "move" on the ItemList class.
    // - We need to capture the step to move from and to in order to swap them.
    // - Access check for both from and to steps?
    \Drupal::messenger()->addMessage($this->t('Step %label was moved successfully', [
      '%label' => $paragraph->field_title->value,
    ]));
  }

  /**
   * Delete a step.
   *
   * @throws \Exception
   */
  public function deleteStep(ParagraphInterface $paragraph) {
    if (!$this->node) {
      throw new \Exception('Digital Form is not set. Cannot sort steps on an empty Digital Form object.');
    }
    $label = $paragraph->get('field_title')->value;
    // Remove item from the field_chapters field.
    /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $chapters */
    $chapters = $this->node->get('field_chapters');
    /** @var  \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $chapter */
    foreach ($chapters as $delta => $chapter) {
      if ($paragraph->id() === $chapter->entity->id()) {
        $chapters->removeItem($delta);
        break;
      }
    }

    // Save the node.
    $this->node->save();

    // Delete the paragraph.
    $paragraph->delete();
    \Drupal::messenger()->addWarning($this->t('Step %label was deleted successfully', [
      '%label' => $label,
    ]));
  }

  /**
   *
   */
  public function stepActionAccess(ParagraphInterface $paragraph, string $action) {
    return AccessResult::allowed();
  }

   public function stepMoveUpAccess() {
     return AccessResult::forbidden();
   }

   public function stepMoveDownAccess() {
     return AccessResult::allowed();
   }

   public function stepDeleteAccess() {
     return AccessResult::allowed();
   }

  /**
   * @return array|void
   */
   public function buildAdditionalSteps() {
     $steps = [];
    foreach ($this->getNonStandarddSteps() as $step) {
      assert(!empty($step['paragraph']) && $step['paragraph'] instanceof ParagraphInterface);
      $paragraph = $step['paragraph'];
      $additional_step = [];
      $additional_step['type'] = $step['type'];
      $additional_step['title'] = $paragraph->get('field_title')->value;
      $additional_step['status'] = $this->getStepStatus('custom', $step['paragraph']);
      $additional_step['url'] = Url::fromRoute('va_gov_form_builder.step.layout', [
        'nid' => $this->id(),
        'stepParagraphId' => $paragraph->id(),
      ])->toString();

      // Determine available actions.
      $additional_step['actions'] = [];
      if ($this->stepMoveUpAccess()) {
        $additional_step['actions'][] = [
          'url' => Url::fromRoute('va_gov_form_builder.step_action', [
            'node' => $this->id(),
            'paragraph' => $paragraph->id(),
            'action' => 'moveup',
          ]),
          'title' => $this->t('Move up'),
          'action' => 'moveup',
        ];
      }
      if ($this->stepMoveDownAccess()) {
        $additional_step['actions'][] = [
          'url' => Url::fromRoute('va_gov_form_builder.step_action', [
            'node' => $this->id(),
            'paragraph' => $paragraph->id(),
            'action' => 'movedown',
          ]),
          'action' => 'movedown',
          'title' => $this->t('Move down'),
        ];
      }
      if ($this->stepDeleteAccess()) {
        $additional_step['actions'][] = [
          'url' => Url::fromRoute('va_gov_form_builder.step_action', [
            'node' => $this->id(),
            'paragraph' => $paragraph->id(),
            'action' => 'delete',
          ]),
          'action' => 'delete',
          'title' => $this->t('Delete'),
        ];
      }

      $steps[] = $additional_step;
    }
    return $steps;
   }

}
