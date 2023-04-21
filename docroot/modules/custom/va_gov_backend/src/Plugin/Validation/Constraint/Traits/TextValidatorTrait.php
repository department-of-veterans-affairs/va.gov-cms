<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits;

use Symfony\Component\Validator\Constraint;

/**
 * Validates string and long_text fields according to text type.
 */
trait TextValidatorTrait {

  /**
   * Add a violation message.
   *
   * @param int $delta
   *   The field delta.
   * @param string $message
   *   The violation message.
   * @param array $params
   *   Message parameters.
   */
  public function addViolation(int $delta, string $message, array $params = []) {
    $this->getContext()
      ->buildViolation($message, $params)
      ->atPath((string) $delta . '.value')
      ->addViolation();
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $delta => $item) {
      $type = $item->getFieldDefinition()->getType();
      $fieldValue = $item->getValue();
      $stringValue = $fieldValue['value'] ?? '';
      if (empty($stringValue)) {
        continue;
      }
      elseif ($type === 'text_long' && $fieldValue['format'] !== 'plain_text') {
        $this->validateHtml($stringValue, $constraint, $delta);
      }
      else {
        $this->validateText($stringValue, $constraint, $delta);
      }
    }
  }

  /**
   * Validates plain text.
   *
   * @param string $text
   *   A plain text string to validate.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint we're validating.
   * @param int $delta
   *   The field item delta.
   */
  abstract public function validateText(string $text, Constraint $constraint, int $delta);

  /**
   * Validates HTML.
   *
   * @param string $html
   *   An HTML string to validate.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint we're validating.
   * @param int $delta
   *   The field item delta.
   */
  abstract public function validateHtml(string $html, Constraint $constraint, int $delta);

}
