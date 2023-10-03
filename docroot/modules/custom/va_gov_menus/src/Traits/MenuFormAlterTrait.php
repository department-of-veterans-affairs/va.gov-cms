<?php

namespace Drupal\va_gov_menus\Traits;

/**
 * Provides centralized menu form alter methods.
 */
trait MenuFormAlterTrait {

  /**
   * Hides the description field on certain menu link forms.
   *
   * @param array $form
   *   The form element array.
   */
  public function hideMenuLinkDescriptionField(array &$form): void {
    $form['description']['#access'] = FALSE;
  }

}
