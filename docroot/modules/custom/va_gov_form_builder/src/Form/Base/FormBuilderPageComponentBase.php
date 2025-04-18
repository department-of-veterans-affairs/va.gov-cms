<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Form\FormStateInterface;

/**
 * Abstract base class for Form Builder component pages.
 *
 * The forms defined using this class are the forms that allow
 * Form Builder users to define/edit components that are children
 * of a page paragraph.
 */
abstract class FormBuilderPageComponentBase extends FormBuilderPageBase {

  /**
   * The list of components on the page paragraph.
   *
   * This is an array of paragraphs. This is generally a
   * single component, but can be more.
   * Ex: A date-range question page has two date components.
   *
   * @var \Drupal\paragraphs\Entity\Paragraph[]
   */
  protected $components;

  /**
   * Returns the child-component fields accessed by this form.
   */
  abstract protected function getComponentFields();

  /**
   * Sets (creates or updates) the components object from the form-state data.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  abstract protected function setComponentsFromFormState(FormStateInterface $form_state);

  /**
   * Returns a field value from the component data.
   *
   * All values are returned as an array, in order
   * to accommodate the variable number of components.
   *
   * If components is not set, returns an empty array.
   * If `fieldName` does not exist, or another error occurs,
   * returns NULL for the curent index of the returned array.
   *
   * This is primarily used to populate forms with default values
   * when the form edit existing components.
   *
   * @param string $fieldName
   *   The name of the field whose value should be fetched.
   *
   * @return mixed[]
   *   An array of values.
   */
  protected function getComponentParagraphFieldValue($fieldName) {
    if (empty($this->components)) {
      return [];
    }

    return array_map(function ($component) use ($fieldName) {
      try {
        return $component->get($fieldName)->value;
      }
      catch (\Exception $e) {
        return NULL;
      }
    }, $this->components);
  }

  /**
   * {@inheritdoc}
   */
  protected function setPageParagraphFromFormState(FormStateInterface $form_state) {
    /*
     * In create mode, the user will have entered the page information
     * on the previous page, and it will be saved in session storage.
     * We apply the title and body from session, and set the components
     * from the form state.
     */
    if ($this->isCreate) {
      $sessionData = $this->session->get(self::SESSION_KEY);
      $title = $sessionData['title'];
      $body = $sessionData['body'];
      $this->setComponentsFromFormState($form_state);

      $this->pageParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_page',
        self::FIELD_KEYS['title'] => $title,
        self::FIELD_KEYS['body'] => $body,
        self::FIELD_KEYS['components'] => $this->components,
      ]);
    }

    /*
     * In edit mode, we do not need to touch the page paragraph at all,
     * as we will only save the existing components that are already
     * attached to the page paragraph.
     *
     * So, simply return here. Nothing to do if not create mode.
     */
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*
     * In create mode, we need to validate the page paragraph.
     * This should already be valid, as the data should not
     * have been successfully stored in the session if it were
     * not valid, but double-check here.
     */
    if ($this->isCreate) {
      // This will set the paragraph as well as its components.
      $this->setPageParagraphFromFormState($form_state);

      /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
      $violations = $this->pageParagraph->validate();

      if ($violations->count() > 0) {
        // Set error that isn't tied to a specific field, since the field.
        $form_state->setErrorByName(
          '',
          $this->t('There was an error with the page title or body. Return to the previous page and edit those values as needed.'
        ));
      }
    }

    /*
     * In edit mode, we need to validate only the components.
     */
    else {
      $this->setComponentsFromFormState($form_state);
    }

    /*
     * In both edit and create modes, we need to validate
     * the form fields for the component being created.
     */
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    foreach ($this->components as $i => $component) {
      $violations = $component->validate();
      if ($violations->count() > 0) {
        xdebug_var_dump($violations);
        exit;
      }
      $this->setFormErrors(
        $form_state,
        $violations,
        $this->getComponentFields(),
        $i,
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
     * In create mode, we need to:
     * 1. Add the page to the step.
     * 2. Save the step.
     * 3. Clear the session value.
     */
    if ($this->isCreate) {
      $this->stepParagraph->get('field_digital_form_pages')->appendItem($this->pageParagraph);
      $this->stepParagraph->save();
      $this->session->set(self::SESSION_KEY, NULL);
    }

    /*
     * In edit mode, we need to:
     * 1. Save the components.
     */
    else {
      foreach ($this->components as $component) {
        $component->save();
      }
    }
  }

}
