<?php

namespace Drupal\va_gov_content_release\Strategy\Resolver;

/**
 * An interface for the strategy resolver.
 */
interface ResolverInterface {

  const STRATEGY_GITHUB_REPOSITORY_DISPATCH = 'github_repository_dispatch';
  const STRATEGY_LOCAL_FILESYSTEM_BUILD_FILE = 'local_filesystem_build_file';

  /**
   * Trigger the content release using the appropriate strategy.
   */
  public function triggerContentRelease() : void;

}
