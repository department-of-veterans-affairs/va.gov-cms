<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph;

use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;

/**
 * Paragraph of type digital_form_list_loop.
 */
class ListLoopParagraph extends FormBuilderParagraphBase {

  /**
   * {@inheritDoc}
   */
  public function getFieldEntities(): array {
    $parentFieldEntities = parent::getFieldEntities();
    return array_filter($parentFieldEntities, function ($sibling) {
      return !in_array($sibling->bundle(), array_values(DigitalForm::STANDARD_STEPS));
    });
  }

}
