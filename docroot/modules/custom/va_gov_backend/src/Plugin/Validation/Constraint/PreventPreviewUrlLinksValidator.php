<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\TextValidatorTrait;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventPreviewUrlLinks constraint.
 */
class PreventPreviewUrlLinksValidator extends ConstraintValidator {

  use TextValidatorTrait;
  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validateText(string $text, Constraint $constraint, int $delta) {
    /*
     * Regular expression explanation:
     *
     * #                Begin delimiter (replace '/', since we're mucking about
     *                  with URLs, which contain slashes.)
     *   (              Open capture group.  We capture the entire URL so that
     *                  we can refer to it in the error message.
     *     https?       Match either 'http' or 'https' ...
     *     ://          ... followed by a colon and two slashes ...
     *     preview-(staging|prod)
     *     .vfs.va.gov  ... followed by a preview servers for prod|staging ...
     *     [^\s]+       ... and any non-whitespace characters that follow.
     *   )              Close capture group.
     * #                End delimiter (replace '/')
     *
     * In other words, we look for a string that looks like a preview URL.
     */
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventPreviewUrlLinks $constraint */
    if (preg_match('#(https?://preview-(staging|prod).vfs.va.gov[^\s]+)#', $text, $matches)) {
      $this->addViolation($delta, $constraint->plainTextMessage, [
        ':url' => $matches[1],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateHtml(string $html, Constraint $constraint, int $delta) {
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventPreviewUrlLinks $constraint */
    $dom = Html::load($html);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a[contains(@href, "preview-staging.vfs.va.gov") or contains(@href, "preview-prod.vfs.va.gov")]') as $element) {
      // Ignore non-element nodes.
      if (!($element instanceof \DOMElement)) {
        continue;
      }
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
