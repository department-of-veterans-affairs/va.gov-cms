<?php

namespace Drupal\va_gov_backend\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Implementing our JSON api.
 */
class VaGovSideNavController {

  /**
   * Callback for the API.
   */
  public function renderApi() {

    return new JsonResponse([
      'data' => $this->getMenuNames(),
      'method' => 'GET',
    ]);
  }

  /**
   * A helper function returning names of system menus.
   */
  public function getMenuNames() {
    $menus_array = menu_ui_get_menus(FALSE);
    $systems_menus = [];
    // Returns an array of drupal menu machine names.
    foreach ($menus_array as $key => $value) {
      if (strpos($key, 'va') !== FALSE || strpos($key, 'pittsburgh-health-care') !== FALSE) {
        $systems_menus[] = $key;
      }
    }
    return $systems_menus;
  }

}
