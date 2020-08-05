<?php

namespace Drupal\va_gov_content_export;

use Drupal\Core\Site\Settings;

/**
 * SiteStatus Service.
 */
class SiteStatus implements SiteStatusInterface {

  /**
   * {@inheritDoc}
   */
  public function inDeployMode(): bool {
    return Settings::get('va_site_deploy_mode', FALSE);
  }

}
