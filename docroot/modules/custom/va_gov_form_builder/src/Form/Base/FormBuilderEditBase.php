<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Abstract base class for Form Builder form steps that edit an existing node.
 */
abstract class FormBuilderEditBase extends FormBuilderNodeBase {

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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    // When form is first built, initialize flag to false.
    $this->digitalFormNodeIsChanged = FALSE;

    // Load the node indicated by the passed-in node id.
    $digitalFormNode = $this->entityTypeManager->getStorage('node')->load($nid);
    if (!$digitalFormNode) {
      throw new NotFoundHttpException();
    }
    $this->digitalFormNode = $digitalFormNode;

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

}
