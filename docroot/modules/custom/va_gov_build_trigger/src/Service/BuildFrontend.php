<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\node\NodeInterface;

/**
 * Class for processing facility status to GovDelivery Bulletin.
 */
class BuildFrontend {

  const WEB_ENVIRONMENTS = [
    'prod' => 'https://www.va.gov',
    'staging' => 'https://staging.va.gov',
    'dev' => 'https://dev.va.gov',
  ];


  /**
   * BuildFrontend constructor.
   */
  public function __construct() {

  }

  /**
   * Get the WEB Url for a desired environment type.
   *
   * @param string $environment_type
   *   The environment type.
   *
   * @retrurn string
   *   The location of the frontend web for the environment.
   */
  public function getWebUrl($environment_type) {
    $cms_url = !empty(self::WEB_ENVIRONMENTS[$environment_type]) ?
      self::WEB_ENVIRONMENTS[$environment_type] :
      getenv('HTTP_HOST');

    // If this is not a Prod environment, link to /static site.
    if (empty(self::WEB_ENVIRONMENTS[$environment_type])) {
      $cms_url .= "/static";
    }

    return $cms_url;
  }

}
