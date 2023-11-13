<?php

namespace Drupal\va_gov_content_release\Strategy\Resolver;

use Drupal\va_gov_content_release\Exception\CouldNotDetermineStrategyException;
use Drupal\va_gov_environment\Discovery\DiscoveryInterface;

/**
 * The strategy resolver service.
 *
 * This service determines, based on the current environment, which strategy
 * should be used to trigger a content release.
 */
class Resolver implements ResolverInterface {

  /**
   * The environment discovery service.
   *
   * @var \Drupal\va_gov_environment\Discovery\DiscoveryInterface
   */
  protected $environmentDiscovery;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_environment\Discovery\DiscoveryInterface $environmentDiscovery
   *   The environment discovery service.
   */
  public function __construct(
    DiscoveryInterface $environmentDiscovery
  ) {
    $this->environmentDiscovery = $environmentDiscovery;
  }

  /**
   * {@inheritDoc}
   */
  public function getStrategyId() : string {
    $environment = $this->environmentDiscovery->getEnvironment();
    return match (TRUE) {
      $environment->isBrd() => static::STRATEGY_GITHUB_REPOSITORY_DISPATCH,
      $environment->isTugboat() => static::STRATEGY_LOCAL_FILESYSTEM_BUILD_FILE,
      $environment->isLocalDev() => static::STRATEGY_LOCAL_FILESYSTEM_BUILD_FILE,
      default => throw new CouldNotDetermineStrategyException('Could not determine a valid content release strategy for environment: ' . $environment->getRawValue()),
    };
  }

}
