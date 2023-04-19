<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\TextValidatorTrait;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventDomainsAsPathsInLinks constraint.
 */
class PreventDomainsAsPathsInLinksValidator extends ConstraintValidator {

  use TextValidatorTrait;
  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validateText(string $text, Constraint $constraint, int $delta) {
    /*
     * Regular expression explanation:
     *
     * #              Begin delimiter (replace '/', since we're mucking about
     *                with URLs, which contain slashes.)
     *   [\s"\']      Match any whitespace or quotation mark...
     *   (            Open capture group for reporting...
     *     /www\.     ...followed by an exact 'www.'...
     *     [^\s"\']+  ...and anything else that isn't a quotation mark or
     *                whitespace.
     *   )            Close capture group.
     * #              End delimiter (replace '/')
     *
     * In other words, we look for a string like `/www.navy.mil/something` or
     * `/www.whitehouse.gov/something-else`, but not a path with a domain-name
     * beyond the first level, like `/some-path/www.example.com/`.
     */
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinks $constraint */
    if (preg_match('#[\s"\'](/www\.[^\s"\']+)#', $text, $matches)) {
      $this->addViolation($delta, $constraint->plainTextMessage, [
        ':url' => $matches[1],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateHtml(string $html, Constraint $constraint, int $delta) {
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinks $constraint */
    $dom = Html::load($html);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a[starts-with(@href, "/www.")]') as $element) {
      $url = $element->getAttribute('href');
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
