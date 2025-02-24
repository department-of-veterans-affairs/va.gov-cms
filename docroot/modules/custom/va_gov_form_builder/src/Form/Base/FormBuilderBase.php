<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract base class for Form Builder form steps.
 */
abstract class FormBuilderBase extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Digital Forms service.
   *
   * @var \Drupal\va_gov_form_builder\Service\DigitalFormsService
   */
  protected $digitalFormsService;

  /**
   * The DigitalForm object created or loaded by this form step.
   *
   * @var \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm
   */
  protected $digitalForm;

  /**
   * Flag indicating if the Digital Form has been changed.
   *
   * Indicates if the Digital Form has been changed
   * since the form was first instantiated.
   *
   * @var bool
   */
  protected $digitalFormIsChanged;

  /**
   * Flag indicating whether this form allows an empty DigitalForm object.
   *
   * This defaults to FALSE. The only time an empty object
   * should be allowed is on the form that creates
   * the node for the first time. Every other form should
   * operate on an existing form and should require an
   * object to be populated.
   *
   * @var bool
   */
  protected $allowEmptyDigitalForm;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, DigitalFormsService $digitalFormsService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->digitalFormsService = $digitalFormsService;
    $this->allowEmptyDigitalForm = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('va_gov_form_builder.digital_forms_service')
    );
  }

  /**
   * Returns the Digital Form fields accessed by this form step.
   */
  abstract protected function getFields();

  /**
   * Sets (creates or updates) a DigitalForm object from the form-state data.
   */
  abstract protected function setDigitalFormFromFormState(array &$form, FormStateInterface $form_state);

  /**
   * Returns a field value from the Digital Form.
   *
   * If Digital Form is not set, or `fieldName`
   * does not exist, returns NULL. This is primarily
   * used to populate forms with default values when the
   * form edits an existing Digital Form.
   *
   * @param string $fieldName
   *   The name of the field whose value should be fetched.
   */
  protected function getDigitalFormFieldValue($fieldName) {
    if (empty($this->digitalForm)) {
      return NULL;
    }

    try {
      if ($fieldName === 'title') {
        return $this->digitalForm->getTitle();
      }

      return $this->digitalForm->get($fieldName)->value;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL) {
    // When form is first built, initialize flag to false.
    $this->digitalFormIsChanged = FALSE;

    if (empty($digitalForm) && !$this->allowEmptyDigitalForm) {
      throw new \InvalidArgumentException('Digital Form cannot be null.');
    }
    $this->digitalForm = $digitalForm;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->setDigitalFormFromFormState($form, $form_state);

    // Validate the node entity.
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->digitalForm->validate();

    // Loop through each violation and set errors on the form.
    if ($violations->count() > 0) {
      foreach ($violations as $violation) {
        // Account for nested property path(e.g. `field_omb_number.0.value`).
        $fieldName = explode('.', $violation->getPropertyPath())[0];

        // Only concern ourselves with validation of fields used on this form.
        if (in_array($fieldName, $this->getFields())) {
          $message = $violation->getMessage();
          $form_state->setErrorByName($fieldName, $message);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the previously validated Digital Form.
    $this->digitalForm->save();
  }

}
