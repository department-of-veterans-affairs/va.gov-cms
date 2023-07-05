<?php

namespace Drupal\va_gov_content_release\Strategy\Resolver;

/**
 * An interface for the strategy resolver.
 */
interface ResolverInterface {

  const STRATEGY_GITHUB_REPOSITORY_DISPATCH = 'github_repository_dispatch';
  const STRATEGY_LOCAL_FILESYSTEM_BUILD_FILE = 'local_filesystem_build_file';

  /**
   * Get the strategy plugin ID.
   *
   * @return string
   *   The strategy plugin ID.
   *
   * @throws \Drupal\va_gov_content_release\Exception\CouldNotDetermineStrategyException
   *   If we could not determine a valid strategy.
   */
  public function getStrategyId() : string;

  /**
   * Trigger the content release using the appropriate strategy.
   */
  public function triggerContentRelease() : void;

}
