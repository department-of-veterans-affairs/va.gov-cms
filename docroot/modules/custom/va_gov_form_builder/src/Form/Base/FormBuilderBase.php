<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
   * The Digital Form node created or loaded by this form step.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $digitalFormNode;

  /**
   * Flag indicating if the node has been changed.
   *
   * Indicates if the node has been changed
   * since the form was first instantiated.
   *
   * @var bool
   */
  protected $digitalFormNodeIsChanged;

  /**
   * Flag indicating if the form mode is "create".
   *
   * Form mode is "create" (and this value is TRUE)
   * only if the form creates the node for the first time.
   * This is the exception.
   *
   * Form mode is "edit" otherwise, and this value is FALSE.
   *
   * @var bool
   */
  protected $isCreate;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns the Digital Form fields accessed by this form step.
   */
  abstract protected function getFields();

  /**
   * Sets (creates or updates) a Digital Form node from the form-state data.
   */
  abstract protected function setDigitalFormNodeFromFormState(array &$form, FormStateInterface $form_state);

  /**
   * Returns a field value from the Digital Form node.
   *
   * If Digital Form node is not set, or `fieldName`
   * does not exist, returns NULL. This is primarily
   * used to populate forms with default values when the
   * form edits an existing Digital Form node.
   */
  protected function getDigitalFormNodeFieldValue($fieldName) {
    if (empty($this->digitalFormNode)) {
      return NULL;
    }

    try {
      if ($fieldName === 'title') {
        return $this->digitalFormNode->getTitle();
      }

      $field = $this->digitalFormNode->get($fieldName);
      if (!empty($field)) {
        return $field->value;
      }

    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    // When form is first built, initialize flag to false.
    $this->digitalFormNodeIsChanged = FALSE;

    if (empty($node)) {
      // If no node is passed in, this is "create" mode.
      $this->isCreate = TRUE;
    }
    else {
      // If a node is passed in, this is "edit" mode.
      $this->isCreate = FALSE;
      $this->digitalFormNode = $node;
    }

    return $form;
  }

  /**
   * Determines if `digitalFormNode` has a chapter (paragraph) of a given type.
   *
   * @param string $type
   *   The chapter (paragraph) type.
   */
  protected function digitalFormNodeHasChapterOfType($type) {
    $chapters = $this->digitalFormNode->get('field_chapters')->getValue();

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

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->setDigitalFormNodeFromFormState($form, $form_state);

    // Validate the node entity.
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->digitalFormNode->validate();

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
    // Save the previously validated node.
    $this->digitalFormNode->save();
  }

}
