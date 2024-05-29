<?php

namespace Drupal\va_gov_form_engine\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\WebformWizardPage;

/**
 * Provides a 'webform_page_element' element for multi-step wizard pages.
 *
 * @WebformElement(
 *   id = "webform_page_element",
 *   label = @Translation("Wizard Page"),
 *   description = @Translation("Provides a single wizard page."),
 *   category = @Translation("VA Patterns"),
 * )
 *
 * @see \Drupal\webform_example_element\Element\WebformExampleElement
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class WebformPageElement extends WebformWizardPage {

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
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isContainer(array $element) {
    return TRUE;
  }

}
