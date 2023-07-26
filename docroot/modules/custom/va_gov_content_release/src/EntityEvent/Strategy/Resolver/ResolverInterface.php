<?php

namespace Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver;

/**
 * An interface for the strategy resolver.
 */
interface ResolverInterface {

  // Respond to node updates immediately.
  const STRATEGY_ON_DEMAND = 'on_demand';
  // Ignore node updates.
  const STRATEGY_NEVER = 'test_false';

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

}
