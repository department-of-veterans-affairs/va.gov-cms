<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventAbsoluteUrlsAsPathsInLinks constraint.
 */
class PreventAbsoluteUrlsAsPathsInLinksValidator extends ConstraintValidator {

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
    foreach ($items as $delta => $item) {
      $type = $item->getFieldDefinition()->getType();
      $fieldValue = $item->getValue();
      /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteUrlsAsPathsInLinks $constraint */
      if ($type === 'text_long' && $fieldValue['format'] !== 'plain_text') {
        $this->validateHtml($fieldValue['value'], $constraint, $delta);
      }
      else {
        $this->validateText($fieldValue['value'], $constraint, $delta);
      }
    }
  }

  /**
   * Validates plain text.
   *
   * @param string $text
   *   A plain text string to validate.
   * @param \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteUrlsAsPathsInLinks $constraint
   *   The constraint we're validating.
   * @param int $delta
   *   The field item delta.
   */
  public function validateText(string $text, PreventAbsoluteUrlsAsPathsInLinks $constraint, int $delta) {
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
    if (preg_match('#(/https?://[^\s]+)#', $text, $matches)) {
      $this->addViolation($delta, $constraint->plainTextMessage, [
        ':url' => $matches[1],
      ]);
    }
  }

  /**
   * Validates HTML.
   *
   * @param string $html
   *   An HTML string to validate.
   * @param \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventAbsoluteUrlsAsPathsInLinks $constraint
   *   The constraint we're validating.
   * @param int $delta
   *   The field item delta.
   */
  public function validateHtml(string $html, PreventAbsoluteUrlsAsPathsInLinks $constraint, int $delta) {
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
