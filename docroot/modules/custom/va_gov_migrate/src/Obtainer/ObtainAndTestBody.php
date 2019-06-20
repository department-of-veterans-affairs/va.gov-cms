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

        if ($element->find('.usa-grid-full > .columns')->count()) {
          $anomalies[] = AnomalyMessage::TWO_COLUMN_CONTENT;
        }
        if ($element->find('a[href^="#"]')->count()) {
          $anomalies[] = AnomalyMessage::JUMPLINKS;
        }
        elseif ($element->find('a[href*="#"]')->count()) {
          $links = $element->find('a[href*="#"]');
          /** @var \QueryPath\DOMQuery $link */
          foreach ($links as $link) {
            $href = $link->attr('href');
            $url_parts = parse_url($href);
            if (!empty($url_parts['fragment'])) {
              if (empty($url_parts['host']) || $url_parts['host'] == 'www.va.gov') {
                // Discharge-upgrade-instructions is a react page, so it's fine.
                if ($url_parts['path'] != '/discharge-upgrade-instructions/') {
                  AnomalyMessage::make('Anchor link to another page', $title, $url, $href . ': ' . $link->text());
                  break;
                }
              }
            }
          }
        }

        $buttons = $element->find('button');
        /** @var \QueryPath\DOMQuery $button */
        foreach ($buttons as $button) {
          if (!$button->hasClass('usa-accordion-button')) {
            AnomalyMessage::make('<button> not supported', $title, $url, $button->html());
          }
        }

        if ($element->find('a[class="login-required"]')->count()) {
          $anomalies[] = 'Sign-in modal trigger not yet supported';
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
        if ($element->find('.background-color-only')->count()) {
          $anomalies[] = AnomalyMessage::ALERTS_BACKGROUND_COLOR_ONLY;
        }
        if ($element->find('[aria-multiselectable="true"]')->count()) {
          $anomalies[] = AnomalyMessage::MULTI_SELECTABLE;
        }
        $fas = $element->find('[class*="fa-"].fas,[class*="fa-"].far');
        if ($fas->count()) {
          /** @var \QueryPath\DOMQuery $fa */
          foreach ($fas as $fa) {
            // The below looks redundant, but it's needed to perform the test.
            if ($fa->parent('.number')->hasClass('number')) {
              $anomalies[] = AnomalyMessage::FONT_AWESOME_NUMBER_CALLOUTS;
            }
            else {
              $anomalies[] = AnomalyMessage::FONT_AWESOME_SNIPPETS;
            }
          }
        }
      }
    }

    foreach ($anomalies as $anomaly) {
      AnomalyMessage::make($anomaly, $title, $url);
    }

    return $text;
  }

}
