<?php

namespace Drupal\va_gov_form_engine\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'composite_pattern_example' element.
 *
 * @WebformElement(
 *   id = "composite_pattern_example",
 *   label = @Translation("Example composite element"),
 *   description = @Translation("Provides a composite webform element example."),
 *   category = @Translation("VA Patterns"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform_example_composite\Element\WebformExampleComposite
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class CompositePatternExample extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);

    $lines = [];
    $lines[] = ($value['first_name'] ? $value['first_name'] : '') .
      ($value['last_name'] ? ' ' . $value['last_name'] : '') .
      ($value['sex'] || $value['date_of_birth'] ? ' -' : '') .
      ($value['sex'] ? ' ' . $value['sex'] : '') .
      ($value['date_of_birth'] ? ' (' . $value['date_of_birth'] . ')' : '');
    return $lines;
  }

}
