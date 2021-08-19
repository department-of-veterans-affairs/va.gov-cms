<?php

namespace Drupal\va_gov_backend\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Repairs formatting of email address links.
 *
 * @Filter(
 *   id = "va_gov_backend_email_link_repair",
 *   title = @Translation("Email Link Repair"),
 *   description = @Translation("Repairs formatting of email address links."),
 *   settings = {
 *     "title" = TRUE,
 *   },
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class EmailLinkRepairFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    if (strpos($text, '<a href="') === FALSE || strpos($text, '@') === FALSE) {
      return $result;
    }
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a[contains(@href, "@")]') as $element) {
      $url = $element->getAttribute('href');
      $email = filter_var($url, FILTER_VALIDATE_EMAIL);
      if ($email !== FALSE) {
        $element->setAttribute('href', 'mailto:' . $email);
      }
    }
    $result->setProcessedText(Html::serialize($dom));
    return $result;
  }

}
