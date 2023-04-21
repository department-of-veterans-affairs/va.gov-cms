<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\TextValidatorTrait;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventAbsoluteCmsLinks constraint.
 */
class PreventAbsoluteCmsLinksValidator extends ConstraintValidator {

  use TextValidatorTrait;
  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validateText(string $text, Constraint $constraint, int $delta) {
    if (strpos($text, 'cms.va.gov') === FALSE) {
      return;
    }
    // We don't need no stinkin' XPath bc plain text.
    if (preg_match('#((https?:)?(//)?[^\s]*?cms\.va\.gov[^\s]*)#', $text, $matches)) {
      $this->addViolation($delta, $constraint->plainTextMessage, [
        ':url' => $matches[1],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateHtml(string $html, Constraint $constraint, int $delta) {
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteCmsLinks $constraint */
    if (strpos($html, 'cms.va.gov') === FALSE) {
      return;
    }
    $dom = Html::load($html);
    $xpath = new \DOMXPath($dom);
    // DOMXPath doesn't support matches(), so we need to use contains().
    foreach ($xpath->query('//a[contains(@href, "cms.va.gov")]') as $element) {
      $url = $element->getAttribute('href');
      $firstChild = $element->hasChildNodes() ? $element->childNodes[0] : NULL;
      $link = $element->ownerDocument->saveHTML($firstChild ?? $element);
      $this->addViolation($delta, $constraint->richTextMessage, [
        ':link' => $link,
        ':url' => $url,
      ]);
      return;
    }
  }

}
