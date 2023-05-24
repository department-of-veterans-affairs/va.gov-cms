<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\TextValidatorTrait;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventVaGovDomainsAsPathsInLinks constraint.
 */
class PreventVaGovDomainsAsPathsInLinksValidator extends ConstraintValidator {

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
     *   [            Gotta begin with one of the following:
     *     \s         A whitespace character.
     *     "          A double quotation mark.
     *     '          A single quotation mark.
     *   ]
     *   /            A forward slash, which begins a path.
     *   (            Open capture group.  We capture the entire path so that
     *                we can refer to it in the error message.
     *     [^\s/]*    Match any non-whitespace, non-slash characters...
     *     va\.gov    ... and the exact string "va.gov"...
     *     [^\s]*     ... and following non-whitespace characters.
     *   )            Close capture group.
     * #              End delimiter (replace '/')
     *
     * In other words, we look for a string like `/va.gov/something` or
     * `/www.va.gov/something-else`.
     */
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventVaGovDomainsAsPathsInLinks $constraint */
    if (preg_match('#[\s"\']/([^\s/]*va\.gov[^\s]*)#', $text, $matches)) {
      $this->addViolation($delta, $constraint->plainTextMessage, [
        ':url' => $matches[1],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateHtml(string $html, Constraint $constraint, int $delta) {
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventVaGovDomainsAsPathsInLinks $constraint */
    $dom = Html::load($html);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a[starts-with(@href, "/") and contains(@href, "va.gov")]') as $element) {
      $url = $element->getAttribute('href');
      /*
       * Regular expression explanation:
       *
       * #              Begin delimiter (replace '/', since we're mucking about
       *                with URLs, which contain slashes.)
       *   ^            ONLY accept this match at the beginning of the string.
       *   /            A forward slash, which begins a path.
       *   (            Open capture group.  We capture the entire path so that
       *                we can refer to it in the error message.
       *     [^\s/]*    Match any non-whitespace, non-slash characters...
       *     va\.gov    ... and the exact string "va.gov"...
       *     [^\s]*     ... and following non-whitespace characters.
       *   )            Close capture group.
       * #              End delimiter (replace '/')
       *
       * In other words, we look for a string like `/va.gov/something` or
       * `/www.va.gov/something-else`, but not `/some-path/www.va.gov/`.
       */
      if (!preg_match('#^/([^\s/]*va\.gov[^\s]*)#', $url)) {
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
