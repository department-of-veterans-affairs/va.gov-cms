<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Symfony\Component\HttpFoundation\Request;

/**
 * CMS Export plugin.
 */
class CMSExport implements DeployPluginInterface {

  /**
   * {@inheritDoc}
   */
  public function match(Request $request): bool {
    $current_path = $request->getPathInfo();
    if ($current_path === '/cms-export/content') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function run(Request $request, string $app_root, string $site_path): ?bool {
    $i = 1;
  }

}
