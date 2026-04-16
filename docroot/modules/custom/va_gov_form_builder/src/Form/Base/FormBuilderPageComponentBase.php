<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;

/**
 * Abstract base class for Form Builder component pages.
 *
 * The forms defined using this class are the forms that allow
 * Form Builder users to define/edit components that are children
 * of a page paragraph.
 */
abstract class FormBuilderPageComponentBase extends FormBuilderPageBase {

  /**
   * The page data.
   *
   * Keys:
   *  - `title`
   *  - `body`
   *
   * @var mixed[]
   */
  protected $pageData;

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
   * Sets (creates or updates) the components object from the form-state data.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  abstract protected function setComponentsFromFormState(FormStateInterface $form_state);

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    DigitalForm|null $digitalForm = NULL,
    Paragraph|null $stepParagraph = NULL,
    Paragraph|null $pageParagraph = NULL,
    CustomSingleQuestionPageType|null $pageComponentType = NULL,
  ) {
    $form = parent::buildForm(
      $form,
      $form_state,
      $digitalForm,
      $stepParagraph,
      $pageParagraph,
      $pageComponentType
    );

    if (empty($pageParagraph)) {
      // If no page paragraph is passed in, this is "create" mode.
      $this->isCreate = TRUE;
      $this->pageData = $this->session->get(self::SESSION_KEY) ?? [
        'title' => '',
        'body' => '',
      ];
    }
    else {
      // If a page paragraph is passed in, this is "edit" mode.
      $this->isCreate = FALSE;
      $this->pageData = [
        'title' => $this->getPageParagraphFieldValue('field_title'),
        'body' => $this->getPageParagraphFieldValue('field_digital_form_body_text'),
      ];

      $this->components = [];
      $componentParagraphs = $this->pageParagraph->get('field_digital_form_components');
      foreach ($componentParagraphs as $componentParagraph) {
        $paragraph = $this->entityTypeManager
          ->getStorage('paragraph')
          ->load($componentParagraph->get('target_id')->getValue());
        if ($paragraph) {
          $this->components[] = $paragraph;
        }
      }
    }

    $form['#page_title'] = $this->pageData['title'] ?? '';
    $form['#page_body'] = $this->pageData['body'] ?? '';

    return $form;
  }

  /**
   * Returns a field value from the component data.
   *
   * All values are returned as an array, in order
   * to accommodate the variable number of components.
   *
   * If components is not set, returns an empty array.
   * If `fieldName` does not exist, or another error occurs,
   * returns NULL for the current index of the returned array.
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
   * Gets a field value from a paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $option
   *   The paragraph entity.
   * @param string $fieldName
   *   The field name to retrieve the value from.
   *
   * @return mixed
   *   The value from the field, or NULL if unable to retrieve.
   */
  protected function getParagraphFieldValue(ParagraphInterface $option, string $fieldName): mixed {
    try {
      return $option->get($fieldName)->value;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setPageParagraphFromFormState(FormStateInterface $form_state) {
    /*
     * In create mode, we need to create the not only the components
     * but also the page. Setting the page amounts to creating it and
     * adding the components to it.
     */
    if ($this->isCreate) {
      $this->setComponentsFromFormState($form_state);

      $this->pageParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_page',
        'field_title' => $this->pageData['title'],
        'field_digital_form_body_text' => $this->pageData['body'],
        'field_digital_form_components' => $this->components,
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
   * Validates the page paragraph.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function validatePageParagraph(array $form, FormStateInterface $form_state) {
    if (empty($this->pageParagraph)) {
      return;
    }

    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->pageParagraph->validate();

    if ($violations->count() > 0) {
      foreach ($violations as $violation) {
        $fieldName = self::getViolationFieldName($violation);

        if (in_array($fieldName, ['field_title', 'field_digital_form_body_text'])) {
          // This is a violation on the page paragraph. This should not be
          // possible as the page paragraph should have been validated before
          // this point, but we check here just in case. If there is an error,
          // set an error on the form itself rather than an individual field.
          if ($fieldName === 'field_title') {
            $message = $this->t('There was an error with the page title. Return to the previous page and adjust as needed.');
          }
          else {
            $message = $this->t('There was an error with the page body. Return to the previous page and adjust as needed.');
          }
          $form_state->setError(
            $form,
            $message,
          );
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
  }

  /**
   * Validates a single page component.
   *
   * @param int $i
   *   The component index.
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  abstract protected function validateComponent(int $i, array $form, FormStateInterface $form_state);

  /**
   * Validates the page components.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function validateComponents(array $form, FormStateInterface $form_state) {
    if (empty($this->components)) {
      return;
    }

    foreach ($this->components as $i => $component) {
      $this->validateComponent($i, $form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*
     * In create mode, we need to validate the page paragraph
     * as well as the components.
     * The page data should already be valid, as the data should not
     * have been successfully stored in the session if it were
     * not valid, but double-check here.
     */
    if ($this->isCreate) {
      $this->setPageParagraphFromFormState($form_state);
      $this->validatePageParagraph($form, $form_state);
      $this->validateComponents($form, $form_state);
    }

    /*
     * In edit mode, we need to validate only the components.
     */
    else {
      $this->setComponentsFromFormState($form_state);
      $this->validateComponents($form, $form_state);
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

    $this->redirectOnSuccess($form_state);
  }

  /**
   * Redirects the user after a successful form submission.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function redirectOnSuccess(FormStateInterface $form_state) {
    $form_state->setRedirect('va_gov_form_builder.step.home', [
      'nid' => $this->digitalForm->id(),
      'stepParagraphId' => $this->stepParagraph->id(),
    ]);
  }

}
