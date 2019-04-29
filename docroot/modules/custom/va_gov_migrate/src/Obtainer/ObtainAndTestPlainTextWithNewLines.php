<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainPlainTextWithNewLines;
use Drupal\va_gov_migrate\AnomalyMessage;
use QueryPath\DOMQuery;

/**
 * Class ObtainAndTestPlainTextWithNewLines.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainAndTestPlainTextWithNewLines extends ObtainPlainTextWithNewLines {

  protected $title;
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
          $text = $element->html();
          $this->setCurrentFindMethod("findSelector($selector, " . ++$n . ')');
          $this->test($element);
        }
      }
    }

    return $text;
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
    $new_line_tags = ['p', 'br'];
    if ($element->children()->count()) {
      $elements = $element->children();
    }
    else {
      return;
    }
    /* @var \QueryPath\DOMQuery $element */
    foreach ($elements as $element) {
      if (!in_array($element->tag(), $new_line_tags)) {
        switch ($element->tag()) {
          case 'a':
            if (substr($element->attr('href'), -3) == 'pdf') {
              $message = AnomalyMessage::INTRO_TEXT_DOES_NOT_SUPPORT_PDF_LINKS;
            }
            else {
              $message = AnomalyMessage::INTRO_TEXT_DOES_NOT_SUPPORT_LINKS;
            }
            break;

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

}
