<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventAdjacentLinks constraint.
 */
class PreventAdjacentLinksValidator extends ConstraintValidator {

  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $delta => $item) {
      /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAdjacentLinks $constraint */
      $html = $item->getValue()['value'];
      if (stripos($html, '</a><a ') === FALSE) {
        return;
      }
      $dom = Html::load($html);
      $xpath = new \DOMXPath($dom);
      // Retrieve <a> tags whose preceding sibling is also an <a> tag.
      foreach ($xpath->query('//a/preceding-sibling::node()[1][name()="a"]') as $element) {
        $ownerDocument = $element->ownerDocument;
        $nextSibling = $element->nextSibling;
        $link = $ownerDocument->saveHTML($element->hasChildNodes() ? $element->childNodes[0] : NULL);
        $link2 = $ownerDocument->saveHTML($nextSibling->hasChildNodes() ? $nextSibling->childNodes[0] : NULL);
        $this->getContext()
          ->buildViolation($constraint->message, [
            ':link' => strip_tags($link),
            ':link2' => strip_tags($link2),
          ])
          ->atPath((string) $delta . '.value')
          ->addViolation();
        return;
      }
    }
  }

}
