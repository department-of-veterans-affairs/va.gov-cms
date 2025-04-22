<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\ConstraintViolation;
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
   * {@inheritdoc}
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['status_messages'] = [
      '#type' => 'status_messages',
    ];

    return $form;
  }

  /**
   * Gets the field name from the violation.
   *
   * @param \Symfony\Component\Validator\ConstraintViolation $violation
   *   The violation object.
   *
   * @return string
   *   The field name associated with the violation.
   */
  protected static function getViolationFieldName(ConstraintViolation $violation) {
    // Account for nested property path(e.g. `field_omb_number.0.value`).
    return explode('.', $violation->getPropertyPath())[0];
  }

  /**
   * Sets form errors based on validation violations.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Symfony\Component\Validator\ConstraintViolationList $violations
   *   The list of validation violations.
   * @param array $fieldMapping
   *   An array of field names to check for validation violations.
   *   Keys are the field names on the entity, and values are the
   *   corresponding form elements.
   */
  protected static function setFormErrors(
    FormStateInterface $form_state,
    ConstraintViolationList $violations,
    array $fieldMapping,
  ) {
    // Loop through each violation and set errors on the form.
    if ($violations->count() > 0) {
      foreach ($violations as $violation) {
        $fieldName = self::getViolationFieldName($violation);

        // Only concern ourselves with validation of fields in passed-in list.
        if (array_key_exists($fieldName, $fieldMapping)) {
          $message = $violation->getMessage();
          $form_state->setError($fieldMapping[$fieldName], $message);
        }
      }
    }
  }

}
