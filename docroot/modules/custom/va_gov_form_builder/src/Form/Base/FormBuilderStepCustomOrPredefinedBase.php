<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Form\FormStateInterface;

/**
 * Abstract base class for custom-or-predefined step pages.
 */
abstract class FormBuilderStepCustomOrPredefinedBase extends FormBuilderStepBase {

  /**
   * The label of the step.
   *
   * @var string
   */
  protected $stepLabel;

  /**
   * The type of step based on the clicked button.
   *
   * @var string
   */
  protected $stepType;

  /**
   * A mapping of button names to paragraph types.
   *
   * @return array
   *   The mapping.
   */
  abstract protected function getButtonToStepTypeMapping();

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL, $stepParagraph = NULL) {
    // On this form, the step paragraph should be allowed to be empty,
    // since it is always in "create" mode.
    $this->allowEmptyStepParagraph = TRUE;
    $this->isCreate = TRUE;

    // Grab the previously entered step label from session storage.
    $this->stepLabel = $this->session->get(self::SESSION_KEY);

    // Call parent build method.
    return parent::buildForm($form, $form_state, $digitalForm, $stepParagraph);
  }

  /**
   * {@inheritdoc}
   */
  protected function setStepParagraphFromFormState(FormStateInterface $form_state) {
    if (!empty($this->stepType)) {
      $this->stepParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => $this->stepType,
        'field_title' => $this->stepLabel,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function validateStepParagraph(array $form, FormStateInterface $form_state) {
    if (!isset($this->stepParagraph)) {
      return;
    }

    // Validate the step paragraph.
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->stepParagraph->validate();

    foreach ($violations as $violation) {
      $fieldName = self::getViolationFieldName($violation);

      if ($fieldName === 'field_title') {
        // This is a violation on the step label. This should not be
        // possible as the step label should have been validated before
        // this point, but we check here just in case. If there is an error,
        // set an error on the form itself rather than an individual field.
        $form_state->setError(
          $form,
          $this->t('There was an error with the step label. Return to the previous page and adjust as needed.'),
        );
      }
      elseif ($fieldName === 'field_digital_form_pages') {
        // Do nothing. This is allowed to be empty here, despite it being a
        // validation error. It *should* always be empty at this point
        // of creating the step.
      }
      else {
        // Some other error. Again, this should not be possible, but we check
        // here just in case. If there is an error, set an error on the
        // form itself rather than an individual field.
        $form_state->setError(
          $form,
          $this->t('There was an error. Please check all the fields and try again.'),
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $clickedButton = $form_state->getTriggeringElement()['#name'];
    $mapping = $this->getButtonToStepTypeMapping();

    if (isset($mapping[$clickedButton])) {
      $this->stepType = $mapping[$clickedButton];
    }
    else {
      $form_state->setError(
        $form,
        'Invalid step type',
      );
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->digitalForm->get('field_chapters')->appendItem($this->stepParagraph);
    $this->digitalForm->save();

    // Clear the session variable after successful paragraph save.
    $this->session->set(self::SESSION_KEY, NULL);
  }

}
