<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainHtml;

/**
 * Class ObtainImageFile.
 *
 * Contains logic for parsing for iframe contents in HTML.
 */
class ObtainIframeVideo extends ObtainHtml {

  /**
   * Find iframes in contents of the selector and put each element in an array.
   *
   * @param string $selector
   *   The selector to find.
   *
   * @return array
   *   The array of elements found containing element, src, alt, title, base_uri
   */
  protected function findIframes($selector) {
    return $this->pluckIframes($selector, FALSE);
  }

  /**
   * Pluck iframes in contents of the selector, put each element in an array.
   *
   * @param string $selector
   *   The selector to find.
   * @param bool $pluck
   *   (optional) Used internally to declare if the items should be removed.
   *
   * @return array
   *   The array of elements found containing element, src, alt, title, base_uri
   */
  protected function pluckIframes($selector, $pluck = TRUE) {
    $found = [];
    if (!empty($selector)) {
      $element_with_iframes = $this->queryPath->find($selector);
      // Get iframes.
      $elements = $element_with_iframes->find('iframe');
      foreach ((is_object($elements)) ? $elements : [] as $element) {
        if ($element->hasAttr('src')) {
          $src = $element->attr('src');
          $title = $element->attr('title');
          if (preg_match('/www.youtube.com\/embed\/([^\?]+)\?/', $src, $matches)) {
            $url = 'https://www.youtube.com/watch?v=' . $matches[1];
          }
          else {
            $url = '';
          }

          $found[] = [
            'element' => $element,
            'src' => $src,
            'title' => $title,
            'url' => $url,
          ];
        }
        $this->setCurrentFindMethod("pluckIframes($selector" . ')');
      }
      if ($pluck) {
        $this->setElementToRemove($elements);
      }
    }

    return $found;
  }

  /**
   * Evaluates $found array and if it checks out, returns TRUE.
   *
   * This method is misleadingly named since it is processing an array, but
   * must override the string based validateString.
   *
   * @param mixed $found
   *   The array to validate.
   *
   * @return bool
   *   TRUE if array is usuable. FALSE if it isn't.
   */
  protected function validateString($found) {
    // Run through any evaluations. If it makes it to the end, it is good.
    // Case race, first to evaluate TRUE aborts the text.
    switch (TRUE) {
      // List any cases below that would cause it to fail validation.
      case empty($found):
      case !is_array($found):

        return FALSE;

      default:
        return TRUE;
    }
  }

  /**
   * Cleans array and returns it prior to validation.
   *
   * This method is misleadingly named since it is processing an array, but
   * must override the string based cleanString.
   *
   * @param mixed $found
   *   Text to clean and return.
   *
   * @return mixed
   *   The cleaned array.
   */
  public static function cleanString($found) {
    $found = (empty($found)) ? [] : $found;
    // Make sure it is an array, just in case someone uses a string finder.
    $found = (is_array($found)) ? $found : [$found];

    return $found;
  }

}
