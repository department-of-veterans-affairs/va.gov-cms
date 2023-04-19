<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\TextValidatorTrait;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventProtocolRelativeLinks constraint.
 */
class PreventProtocolRelativeLinksValidator extends ConstraintValidator {

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
     *     [^         Match anything _not_ in this set:
     *       (        - A string consisting of:
     *         \w+      - One or more word characters, followed by:
     *         :        - a literal colon.
     *       )        End group.
     *     ]          End set.
     *     //         Two literal forward slashes.
     *     [^         Match anything not in the following set:
     *       /        A forward slash.
     *     ]          End set.
     *     +          One or more of the previous.
     *     \w+        One or more word characters.
     *     (          Match an occurence of the following:
     *       \.       A literal dot.
     *       \w+      One or more word characters.
     *     )          End group.
     *     +          One or more of the previous.  This matches things like
     *                `example.com`, or `abc.co.uk`, but not `example.`.
     *     \S*        Zero or more of any non-whitespace character.  This
     *                matches URLs of arbitrary complexity.
     *   )            Close capture group.
     * #              End delimiter (replace '/')
     *
     * In other words, we look for a string that:
     * - does _not_ begin with something looking like a scheme
     *   (http:, https:, gopher:, socks5h:)
     * - begins with two slashes (but no more)
     * - contains at least two "words" separated by dots
     * - may contain arbitrary non-whitespace characters after that.
     */
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinks $constraint */
    if (preg_match('#([^(\w+:)]//[^/]+\w+(\.\w+)+\S*)#', $text, $matches)) {
      $this->addViolation($delta, $constraint->plainTextMessage, [
        ':url' => $matches[1],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateHtml(string $html, Constraint $constraint, int $delta) {
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinks $constraint */
    $dom = Html::load($html);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a[starts-with(@href, "//")]') as $element) {
      $url = $element->getAttribute('href');
      if (strpos($url, '///') === 0) {
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
