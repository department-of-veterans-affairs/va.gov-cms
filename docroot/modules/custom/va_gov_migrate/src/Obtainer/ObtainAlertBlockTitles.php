<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainArray;
use Drupal\va_gov_migrate\AnomalyMessage;

/**
 * Obtainer to perform a little extra cleanup on alert titles.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainAlertBlockTitles extends ObtainArray {

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
    $found = [];
    if (!empty($selector)) {
      $elements = $this->queryPath->find($selector);
      /** @var \QueryPath\DOMQuery $element */
      foreach ((is_object($elements)) ? $elements : [] as $element) {
        $alert_block = $element->parent('.usa-alert');
        if ($alert_block->prev()->count() && $alert_block->prev()->tag() != 'br'
          && !$alert_block->prev()->hasClass('va-introtext') &&
          !$alert_block->prev()->hasClass('feature')) {
          AnomalyMessage::make(AnomalyMessage::ALERTS_IN_BODY, $title, $url);
        }
        $found[] = $element->text();
        $this->setCurrentFindMethod("arrayPluckSelector($selector" . ')');

      }
      $this->setElementToRemove($elements);
    }

    return $found;
  }

  /**
   * {@inheritdoc}
   */
  public static function cleanString($found) {
    $found = parent::cleanString($found);
    $found = array_map(
      function ($value) {
        // Only run if the value is not an array.
        if (!is_array($value)) {
          $parts = self::splitOnBr($value);
          return strip_tags($parts[0]);
        }
        else {
          return $value;
        }
      }, $found
    );

    return $found;
  }

}
