<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\Component\Utility\Html;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PreventDomainsAsPathsInLinks constraint.
 */
class PreventDomainsAsPathsInLinksValidator extends ConstraintValidator {

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
      /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinks $constraint */
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
   * @param \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinks $constraint
   *   The constraint we're validating.
   * @param int $delta
   *   The field item delta.
   */
  public function validateText(string $text, PreventDomainsAsPathsInLinks $constraint, int $delta) {
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
    if (preg_match('#[\s"\'](/www\.[^\s"\']+)#', $text, $matches)) {
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
   * @param \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinks $constraint
   *   The constraint we're validating.
   * @param int $delta
   *   The field item delta.
   */
  public function validateHtml(string $html, PreventDomainsAsPathsInLinks $constraint, int $delta) {
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
