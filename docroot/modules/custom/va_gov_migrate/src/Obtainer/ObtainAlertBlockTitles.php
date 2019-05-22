<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainHtml;
use Drupal\va_gov_migrate\AnomalyMessage;

/**
 * Obtainer to perform a little extra cleanup on alert titles.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainAlertBlockTitles extends ObtainHtml {

  /**
   * Get alert block titles and warn if block is not at the top of the page.
   *
   * @param string $selector
   *   The css selector.
   * @param string $title
   *   Tpe page title.
   * @param string $url
   *   The page url.
   *
   * @return array
   *   The alert block titles found on the page.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function pluckSelectorAndTest($selector, $title, $url) {
    $migrations = \Drupal::service('plugin.manager.migration')->createInstances(['va_alert_block']);
    $found = '';
    if (!empty($selector)) {
      $elements = $this->queryPath->find($selector);
      /** @var \QueryPath\DOMQuery $element */
      foreach ((is_object($elements)) ? $elements : [] as $element) {
        $prev = $element->prev();
        if ($prev->count() && $prev->tag() != 'br' && !$prev->hasClass('va-introtext') && !$prev->hasClass('feature')) {
          AnomalyMessage::make(AnomalyMessage::ALERTS_IN_BODY, $title, $url);
          continue;
        }
        if (empty($found)) {
          $alert_title = $element->find('.usa-alert-heading')->text();

          if ($bid = $migrations['va_alert_block']->getIdMap()->lookupDestinationIds([$alert_title])) {
            $found = $bid[0][0];
            $this->setCurrentFindMethod("arrayPluckSelector($selector" . ')');
            $this->setElementToRemove($element);
          }
        }
        else {
          AnomalyMessage::make(AnomalyMessage::ALERTS_TOP_OF_PAGE, $title, $url);
          break;
        }
      }
    }

    return $found;
  }

  /**
   * {@inheritdoc}
   */
  public static function cleanString($found) {
    return $found;
  }

}
