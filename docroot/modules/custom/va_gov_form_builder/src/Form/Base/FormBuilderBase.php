<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Abstract base class for Form Builder steps.
 */
abstract class FormBuilderBase extends FormBase {

  /**
   * The Form Builder's image directory.
   */
  const IMAGE_DIR = '/modules/custom/va_gov_form_builder/images/';

  /**
   * The session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

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
   * Flag indicating whether this form is in "create" mode.
   *
   * "Create" mode reflects the state where this form is creating
   * an entity for the first time, and should be contrasted
   * with "edit" mode.
   *
   * Defaults to FALSE.
   *
   * @var bool
   */
  protected $isCreate;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, DigitalFormsService $digitalFormsService, SessionInterface $session) {
    $this->entityTypeManager = $entityTypeManager;
    $this->digitalFormsService = $digitalFormsService;
    $this->session = $session;

    $this->isCreate = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('va_gov_form_builder.digital_forms_service'),
      $container->get('session')
    );
  }

  /**
   * Sets form errors based on validation violations.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Symfony\Component\Validator\ConstraintViolationList $violations
   *   The list of validation violations.
   * @param string[] $fieldList
   *   A list of fields to check for validation violations. Violations will
   *   only trigger form errors if the field to which the violation applies
   *   is in this list.
   */
  protected static function setFormErrors(FormStateInterface $form_state, ConstraintViolationList $violations, array $fieldList) {
    // Loop through each violation and set errors on the form.
    if ($violations->count() > 0) {
      foreach ($violations as $violation) {
        // Account for nested property path(e.g. `field_omb_number.0.value`).
        $fieldName = explode('.', $violation->getPropertyPath())[0];

        // Only concern ourselves with validation of fields in passed-in list.
        if (in_array($fieldName, $fieldList)) {
          $message = $violation->getMessage();
          $form_state->setErrorByName($fieldName, $message);
        }
      }
    }
  }

  /**
   * Creates repeatable fields dynamically using AJAX.
   */
  public function addRepeatableFieldGroup(array &$form, FormStateInterface $form_state, string $element_name, array $field_definitions, int $min = 1, int $max = 10) {
    // Get the number of items in the form already.
    $num_items = $form_state->get($element_name . '_count');
    // We have to ensure that there is at least one item field.
    if ($num_items === NULL) {
      $form_state->set($element_name . '_count', 1);
      $num_items = $form_state->get($element_name . '_count');
    }

    // Create the container to hold our dynamic items.
    $form[$element_name . '_fieldset'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $element_name . '-wrapper'],
    ];

    for ($i = 0; $i < $num_items; $i++) {
      // Create a new fields for each item.
      foreach ($field_definitions as $field_key => $definition) {
        $form[$element_name . '_fieldset'][$element_name][$i][$field_key] = $definition;
      }
    }

    if ($num_items < $max) {
      $form[$element_name . '_fieldset']['actions'] = [
        '#type' => 'actions',
      ];

      $form[$element_name . '_fieldset']['actions']['add_item'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add another'),
        '#submit' => ['::addOne'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => $element_name . '-wrapper',
        ],
      ];
    }
  }

  /**
   * Callback for ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    // @todo Make this field dynamic somehow.
    return $form['dynamic_radio_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    // @todo Make this field dynamic somehow.
    $num_field = $form_state->get('dynamic_radios_count');
    $new_count = $num_field + 1;
    $form_state->set('dynamic_radios_count', $new_count);
    $form_state->setRebuild();
  }

}
