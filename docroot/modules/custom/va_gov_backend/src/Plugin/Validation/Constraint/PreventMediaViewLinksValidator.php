<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventMediaViewLinks constraint.
 */
class PreventMediaViewLinksValidator extends ConstraintValidator {

  use ValidatorContextAccessTrait;

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
    /* Drupal\va_gov_backend\Plugin\Validation\Constraint $constraint */
    foreach ($items as $delta => $item) {
      $html = $item->getValue()['value'];
      $dom = Html::load($html);
      $xpath = new \DOMXPath($dom);
      foreach ($xpath->query('//a[starts-with(@href, "/media/")]') as $element) {
        $url = $element->getAttribute('href');
        if (preg_match('#^/media/\d+$#', $url)) {
          $firstChild = $element->hasChildNodes() ? $element->childNodes[0] : NULL;
          $link = $element->ownerDocument->saveHTML($firstChild ?? $element);
          $this->getContext()
            ->buildViolation($constraint->richTextMessage, [
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

}
