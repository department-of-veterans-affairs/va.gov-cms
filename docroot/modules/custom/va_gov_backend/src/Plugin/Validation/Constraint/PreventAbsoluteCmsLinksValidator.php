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
    foreach ($items as $item) {
      $type = $item->getFieldDefinition()->getType();
      $fieldValue = $item->getValue();
      /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinks $constraint */
      if ($type === 'text_long' && $fieldValue['format'] === 'rich_text') {
        $this->validateHtml($fieldValue['value'], $constraint);
      }
      else {
        $this->validateText($fieldValue['value'], $constraint);
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
   */
  public function validateText(string $text, PreventAbsoluteCmsLinks $constraint) {
    if (strpos($text, 'cms.va.gov') === FALSE) {
      return;
    }
    // We don't need no stinkin' XPath bc plain text.
    if (preg_match_all('#((https?:)?//.*?cms\.va\.gov[^\s]*)#', $text, $matches) && !empty($matches[1])) {
      foreach ($matches[1] as $match) {
        $this->context->addViolation($constraint->plainTextMessage, [
          ':url' => $match,
        ]);
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
   */
  public function validateHtml(string $html, PreventAbsoluteCmsLinks $constraint) {
    if (strpos($html, 'cms.va.gov') === FALSE) {
      return;
    }
    $dom = Html::load($html);
    $xpath = new \DOMXPath($dom);
    // DOMXPath doesn't support matches(), so we need to use contains().
    foreach ($xpath->query('//a[contains(@href, "cms.va.gov/")]') as $element) {
      $url = $element->getAttribute('href');
      // XPath contains() is fast but could lead to false positives.
      // The following is slower but will not.
      if (preg_match('#.*?cms\.va.gov$#', parse_url($url, PHP_URL_HOST))) {
        $firstChild = $element->hasChildNodes() ? $element->childNodes[0] : NULL;
        $link = $element->ownerDocument->saveHTML($firstChild ?? $element);
        $this->context->addViolation($constraint->richTextMessage, [
          ':link' => $link,
          ':url' => $url,
        ]);
      }
    }
  }

}
