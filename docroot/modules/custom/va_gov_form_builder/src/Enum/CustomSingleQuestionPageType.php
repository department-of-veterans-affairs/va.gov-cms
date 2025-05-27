<?php

namespace Drupal\va_gov_form_builder\Enum;

/**
 * Enum for the custom single question page types.
 */
enum CustomSingleQuestionPageType: string {
  case SingleDate = 'date.single_date';
  case DateRange = 'date.date_range';
  case Radio = 'choice.radio';
  case Checkbox = 'choice.checkbox';
  case TextInput = 'text.text_input';
  case TextArea = 'text.text_area';
}
