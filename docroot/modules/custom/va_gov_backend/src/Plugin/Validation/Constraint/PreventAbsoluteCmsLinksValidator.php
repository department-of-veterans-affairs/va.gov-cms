<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventAbsoluteCmsLinks constraint.
 */
class PreventAbsoluteCmsLinksValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $delta => $item) {
      $type = $item->getFieldDefinition()->getType();
      $fieldValue = $item->getValue();
      /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinks $constraint */
      if ($type === 'text_long' && $fieldValue['format'] !== 'plain_text') {
        $this->validateHtml($fieldValue['value'], $constraint, $delta);
      }
      else {
        $this->validateText($fieldValue['value'], $constraint, $delta);
      }
    }
  }

  /**
   * Validates plain text.
   *
   * @param string $text
   *   A plain text string to validate.
   * @param \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinks $constraint
   *   The constraint we're validating.
   * @param int $delta
   *   The field item delta.
   */
  public function validateText(string $text, PreventAbsoluteCmsLinks $constraint, int $delta) {
    if (strpos($text, 'cms.va.gov') === FALSE) {
      return;
    }
    // We don't need no stinkin' XPath bc plain text.
    if (preg_match_all('#((https?:)(//)?.*?cms\.va\.gov[^\s]*)#', $text, $matches) && !empty($matches[1])) {
      foreach ($matches[1] as $match) {
        $this->context->buildViolation($constraint->plainTextMessage, [
          ':url' => $match,
        ])
          ->atPath((string) $delta . '.value')
          ->setInvalidValue($text)
          ->addViolation();
      }
    }
  }

  /**
   * Validates HTML.
   *
   * @param string $html
   *   An HTML string to validate.
   * @param \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinks $constraint
   *   The constraint we're validating.
   * @param int $delta
   *   The field item delta.
   */
  public function validateHtml(string $html, PreventAbsoluteCmsLinks $constraint, int $delta) {
    if (strpos($html, 'cms.va.gov') === FALSE) {
      return;
    }
    $dom = Html::load($html);
    $xpath = new \DOMXPath($dom);
    // DOMXPath doesn't support matches(), so we need to use contains().
    foreach ($xpath->query('//a[contains(@href, "cms.va.gov/")]') as $element) {
      $url = $element->getAttribute('href');
      $firstChild = $element->hasChildNodes() ? $element->childNodes[0] : NULL;
      $link = $element->ownerDocument->saveHTML($firstChild ?? $element);
      $this->context->buildViolation($constraint->richTextMessage, [
        ':link' => $link,
        ':url' => $url,
      ])
        ->atPath((string) $delta . '.value')
        ->setInvalidValue($html)
        ->addViolation();
    }
  }

}
