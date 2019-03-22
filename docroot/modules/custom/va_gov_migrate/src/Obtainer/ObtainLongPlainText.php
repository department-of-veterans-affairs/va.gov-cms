<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainHtml;

/**
 * Obtainers for Long Plain Text.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainLongPlainText extends ObtainHtml {

  /**
   * Plucker to turn wysiwyg into long plain text for nth selector on the page.
   *
   * @param string $selector
   *   The selector to find.
   * @param int $n
   *   (optional) The depth to find.  Default: first item n=1.
   *
   * @return string
   *   The text found.
   */
  protected function pluckPlain($selector, $n = 1) {
    $text = '';
    $n = ($n > 0) ? $n - 1 : 0;
    if (!empty($selector)) {
      $elements = $this->queryPath->find($selector);
      foreach ((is_object($elements)) ? $elements : [] as $i => $element) {
        if ($i == $n) {
          $this->setElementToRemove($element);
          $plain_text = '';
          /** @var \QueryPath\DOMQuery $item */
          foreach ($element as $item) {
            if ($item->tag() == 'p') {
              $text = $item->innerHTML() . PHP_EOL . PHP_EOL;
            }
            else {
              $text = $item->html();
            }
            $sections = self::splitOnBr($text);
            $plain_text .= strip_tags(implode(PHP_EOL, $sections));
          }
          $this->setCurrentFindMethod("pluckPlain($selector, " . ++$n . ')');
          break;
        }
      }
    }

    return trim($plain_text);
  }

}
