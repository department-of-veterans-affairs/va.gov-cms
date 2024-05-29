<?php

namespace Drupal\va_gov_form_engine\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\WebformWizardPage;

/**
 * Provides a top-level 'chapter' element for multistep wizard forms.
 *
 * @WebformElement(
 *   id = "chapter",
 *   label = @Translation("Wizard Chapter"),
 *   description = @Translation("Provides a chapter container for multiple sub-pages."),
 *   category = @Translation("VA Patterns"),
 * )
 *
 * @see \Drupal\webform_example_element\Element\WebformExampleElement
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class Chapter extends WebformWizardPage {

  /**
   * {@inheritdoc}
   */
  public function isInput(array $element) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isRoot() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isContainer(array $element) {
    return TRUE;
  }

}
