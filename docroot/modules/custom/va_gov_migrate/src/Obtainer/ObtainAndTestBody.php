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

        $anomalies = [];

        if ($element->find('.usa-grid-full .columns')->count()) {
          $anomalies[] = AnomalyMessage::TWO_COLUMN_CONTENT;
        }
        if ($element->find('a[href^="#"]')->count()) {
          $anomalies[] = AnomalyMessage::JUMPLINKS;
        }
        elseif ($element->find('a[href*="#"]')->count()) {
          $anomalies[] = 'Anchor link to another page';
          AnomalyMessage::make('Anchor link to another page', $title, $url, $element->find('a[href*="#"]')->text());
        }

        if ($element->find('.va-h-ruled--stars')->count()) {
          $anomalies[] = AnomalyMessage::STARRED_DIVIDER;
        }
        if ($element->find('.vertical-list-group')->count()) {
          $anomalies[] = AnomalyMessage::SUBWAY_MAP_WITHOUT_NUMBERS;
        }
        if ($element->find('a.usa-button-primary')->count()) {
          /** @var \QueryPath\DOMQuery $btn_element */
          foreach ($element->find('a.usa-button-primary') as $btn_element) {
            if ($btn_element->prev()->count() && $btn_element->prev()->hasClass('usa-button-primary')) {
              $anomalies[] = AnomalyMessage::TWO_BUTTONS_SIDE_BY_SIDE;
              break;
            }
          }
        }
        if ($element->find('table')->count()) {
          $anomalies[] = AnomalyMessage::TABLES;
        }
        if ($element->find('.background-color-only')->count()) {
          $anomalies[] = AnomalyMessage::ALERTS_BACKGROUND_COLOR_ONLY;
        }
        if ($element->find('[aria-multiselectable="true"]')->count()) {
          $anomalies[] = AnomalyMessage::MULTI_SELECTABLE;
        }
      }
    }

    foreach ($anomalies as $anomaly) {
      AnomalyMessage::make($anomaly, $title, $url);
    }

    return $text;
  }

}
