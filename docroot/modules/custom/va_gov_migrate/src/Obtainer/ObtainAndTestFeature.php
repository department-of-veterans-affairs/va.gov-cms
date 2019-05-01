<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainHtml;
use Drupal\va_gov_migrate\AnomalyMessage;

/**
 * Class ObtainAndTestFeature.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainAndTestFeature extends ObtainHtml {

  /**
   * Get feature and warn if there's more than one or it's in the body.
   *
   * @param string $selector
   *   The css selector.
   * @param string $title
   *   Tpe page title.
   * @param string $url
   *   The page url.
   * @param string $method
   *   (optional) The method to use on the element, text or html. Default: text.
   *
   * @return array
   *   The alert block titles found on the page.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function pluckSelectorAndTest($selector, $title, $url, $method = 'text') {
    $text = '';
    if (!empty($selector)) {
      /** @var \QueryPath\DOMQuery $elements */
      $elements = $this->queryPath->find($selector);

      if (is_object($elements) && $elements->count()) {
        $element = $elements->first();
        $this->setElementToRemove($element);
        $text = $element->$method();
        $this->setCurrentFindMethod("pluckSelector($selector, " . 1 . ')');

        if ($element->prev()->count() && !$element->prev()->hasClass('va-introtext') && !$element->prev()->tag() == 'br') {
          AnomalyMessage::make(AnomalyMessage::FEATURED_NOT_AT_TOP_OF_CONTENT, $title, $url);
        }
        if ($elements->count() > 1) {
          AnomalyMessage::make(AnomalyMessage::FEATURED_MORE_THAN_ONE, $title, $url);
        }
      }
    }

    return $text;
  }

}
