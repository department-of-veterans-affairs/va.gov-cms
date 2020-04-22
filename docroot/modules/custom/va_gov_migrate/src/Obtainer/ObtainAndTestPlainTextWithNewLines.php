<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainPlainTextWithNewLines;
use Drupal\migration_tools\StringTools;
use Drupal\va_gov_migrate\AnomalyMessage;
use QueryPath\DOMQuery;

/**
 * Class ObtainAndTestPlainTextWithNewLines.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainAndTestPlainTextWithNewLines extends ObtainPlainTextWithNewLines {

  /**
   * Title.
   *
   * @var string
   */
  protected $title;

  /**
   * Url.
   *
   * @var string
   */
  protected $url;

  /**
   * {@inheritdoc}
   */
  protected function pluckSelectorAndTest($selector, $title, $url, $n = 1) {
    $this->title = $title;
    $this->url = $url;

    $text = '';
    $n = ($n > 0) ? $n - 1 : 0;
    if (!empty($selector)) {
      $elements = $this->queryPath->find($selector);
      /* @var \QueryPath\DOMQuery $element */
      foreach ((is_object($elements)) ? $elements : [] as $i => $element) {
        if ($i == $n) {
          $this->test($element);
          break;
        }
      }
    }

    return $this->pluckSelector($selector, $n);
  }

  /**
   * Test for html elements inside plain text areas.
   *
   * @param \QueryPath\DOMQuery $element
   *   The element to test.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function test(DOMQuery $element) {
    $allowed_tags = ['a', 'em', 'strong', 'p', 'br'];
    if ($element->children()->count()) {
      $elements = $element->children();
    }
    else {
      return;
    }
    /* @var \QueryPath\DOMQuery $element */
    foreach ($elements as $element) {
      if (!in_array($element->tag(), $allowed_tags)) {
        switch ($element->tag()) {
          case 'ul':
            $message = AnomalyMessage::INTRO_TEXT_DOES_NOT_SUPPORT_BULLETS;
            break;

          default:
            $message = 'Intro text does not support ' . $element->tag();
            break;
        }
        AnomalyMessage::make($message, $this->title, $this->url);
      }
      else {
        $this->test($element);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function cleanString($string) {

    $string = strip_tags($string, '<a><em><strong><p><br>');

    // There are also numeric html special chars, let's change those.
    $string = StringTools::decodeHtmlEntityNumeric($string);
    // Checking again in case another process rendered it non UTF-8.
    $is_utf8 = mb_check_encoding($string, 'UTF-8');

    if (!$is_utf8) {
      $string = StringTools::fixEncoding($string);
    }

    // Remove white space-like things from the ends and decodes html entities.
    // This also removes new lines at the beginning and end of the string added
    // by the p tag replacement above.
    $string = StringTools::superTrim($string);

    return $string;
  }

}
