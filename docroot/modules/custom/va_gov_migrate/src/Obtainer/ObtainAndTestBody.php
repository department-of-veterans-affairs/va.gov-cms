<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainHtml;
use Drupal\va_gov_migrate\AnomalyMessage;

/**
 * Class ObtainAndTestBody.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainAndTestBody extends ObtainHtml {

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

        if ($element->find('.usa-grid-full columns')->count()) {
          AnomalyMessage::make(AnomalyMessage::TWO_COLUMN_CONTENT, $title, $url);
        }
        if ($element->find('ul a[href^="#"]')->count()) {
          AnomalyMessage::make(AnomalyMessage::JUMPLINKS, $title, $url);
        }
        if ($element->find('.va-h-ruled--stars')->count()) {
          AnomalyMessage::make(AnomalyMessage::STARRED_DIVIDER, $title, $url);
        }
        if ($element->find('.vertical-list-group')->count()) {
          AnomalyMessage::make(AnomalyMessage::SUBWAY_MAP_WITHOUT_NUMBERS, $title, $url);
        }
        if ($element->find('[class^="fa-"]')->count()) {
          $fa_elements = $element->find('[class^="fa-"]');
          /** @var \QueryPath\DOMQuery $fa */
          foreach ($fa_elements as $fa) {
            if ($fa->parent('.vertical-list-group')->count()) {
              AnomalyMessage::make(AnomalyMessage::FONT_AWESOME_NUMBER_CALLOUTS, $title, $url);
            }
            else {
              AnomalyMessage::make(AnomalyMessage::FONT_AWESOME_SNIPPETS, $title, $url);
            }
          }
        }
      }
    }

    return $text;
  }

}
