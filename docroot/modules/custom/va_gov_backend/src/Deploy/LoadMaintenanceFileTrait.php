<?php

namespace Drupal\va_gov_backend\Deploy;

/**
 * Trait to load in maintenance html file.
 */
trait LoadMaintenanceFileTrait {

  /**
   * Load the static html maintenance file.
   *
   * @param string $app_root
   *   The Drupal app root.
   * @param string $site_path
   *   The Drupal site path.
   *
   * @return string|null
   *   html to use as the page response.
   */
  protected function loadMaintenanceHtml(string $app_root, string $site_path) : ?string {
    // @TODO make this configurable.
    $file = $app_root . '/' . $site_path . '/maintenance.html';
    // We don't have drupal file schema set up yet so we do this old school.
    if (file_exists($file)) {
      return file_get_contents($file);
    }

    return NULL;
  }

}
