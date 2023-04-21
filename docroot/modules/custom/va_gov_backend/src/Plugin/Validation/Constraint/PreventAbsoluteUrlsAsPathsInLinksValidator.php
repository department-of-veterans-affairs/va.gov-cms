<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\TextValidatorTrait;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventAbsoluteUrlsAsPathsInLinks constraint.
 */
class PreventAbsoluteUrlsAsPathsInLinksValidator extends ConstraintValidator {

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
     *   (            Open capture group.  We capture the entire URL so that
     *                we can refer to it in the error message.
     *     /          Look for a leading slash.
     *     https?     Match either 'http' or 'https'...
     *     ://        ... followed by a colon and two slashes...
     *     [\s]+      ... and any non-whitespace characters that follow.
     *   )            Close capture group.
     * #              End delimiter (replace '/')
     *
     * In other words, we look for a string that looks like a valid URL but is
     * immediately preceded by a forward slash.
     */
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteUrlsAsPathsInLinks $constraint */
    if (preg_match('#(/https?://[^\s]+)#', $text, $matches)) {
      $this->addViolation($delta, $constraint->plainTextMessage, [
        ':url' => $matches[1],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateHtml(string $html, Constraint $constraint, int $delta) {
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteUrlsAsPathsInLinks $constraint */
    $dom = Html::load($html);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a[starts-with(@href, "/http")]') as $element) {
      $url = $element->getAttribute('href');
      // False alarm!  Maybe page alias is `/https-better-than-http`!
      if (strpos($url, '//http:') !== FALSE && strpos($url, '//https:') !== FALSE) {
        continue;
      }
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
