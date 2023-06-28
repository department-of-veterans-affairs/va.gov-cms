<?php

namespace Drupal\va_gov_content_release\Strategy\Resolver;

/**
 * An interface for the strategy resolver.
 */
interface ResolverInterface {

  /**
   * Trigger the content release using the appropriate strategy.
   */
  public function triggerContentRelease() : void;

}
