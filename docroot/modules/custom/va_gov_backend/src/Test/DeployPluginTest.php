<?php

namespace Drupal\va_gov_backend\Test;

use Drupal\va_gov_backend\Deploy\Plugin\DeployPluginInterface;
use Drupal\va_gov_backend\Deploy\SuccessHTTPException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mock class to test DeployService.
 */
class DeployPluginTest implements DeployPluginInterface {

  /**
   * {@inheritDoc}
   */
  public function match(Request $request): bool {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function run(Request $request, string $app_root, string $site_path) {
    throw new SuccessHTTPException('new error');
  }

}
