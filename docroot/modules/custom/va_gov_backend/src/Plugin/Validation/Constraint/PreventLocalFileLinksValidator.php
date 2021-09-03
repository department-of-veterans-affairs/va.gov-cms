<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventLocalFileLinks constraint.
 */
class PreventLocalFileLinksValidator extends ConstraintValidator {

  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $delta => $item) {
      /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventLocalFileLinks $constraint */
      $html = $item->getValue()['value'];
      if (strpos($html, '///') === FALSE) {
        return;
      }
      $dom = Html::load($html);
      $xpath = new \DOMXPath($dom);
      // DOMXPath doesn't support matches(), so we need to use contains().
      foreach ($xpath->query('//a[starts-with(@href, "///")]') as $element) {
        $url = $element->getAttribute('href');
        $firstChild = $element->hasChildNodes() ? $element->childNodes[0] : NULL;
        $link = $element->ownerDocument->saveHTML($firstChild ?? $element);
        $this->getContext()
          ->buildViolation($constraint->message, [
            ':link' => $link,
            ':url' => $url,
          ])
          ->atPath((string) $delta . '.value')
          ->addViolation();
        return;
      }
    }
  }

}
