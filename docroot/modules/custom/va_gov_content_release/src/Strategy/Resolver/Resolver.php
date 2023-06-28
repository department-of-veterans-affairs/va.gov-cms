<?php

namespace Drupal\va_gov_content_release\Strategy\Resolver;

use Drupal\va_gov_environment\Service\DiscoveryInterface;
use Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginManagerInterface;
use Drupal\va_gov_content_release\Exception\CouldNotDetermineStrategyException;

/**
 * The strategy resolver service.
 *
 * This service determines, based on the current environment, which strategy
 * should be used to trigger a content release.
 */
class Resolver implements ResolverInterface {

  /**
   * The strategy plugin manager.
   *
   * @var \Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginManagerInterface
   */
  protected $strategyPluginManager;

  /**
   * The environment discovery service.
   *
   * @var \Drupal\va_gov_environment\Service\DiscoveryInterface
   */
  protected $environmentDiscovery;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginManagerInterface $strategyPluginManager
   *   The strategy plugin manager.
   * @param \Drupal\va_gov_environment\Service\DiscoveryInterface $environmentDiscovery
   *   The environment discovery service.
   */
  public function __construct(
    StrategyPluginManagerInterface $strategyPluginManager,
    DiscoveryInterface $environmentDiscovery
  ) {
    $this->strategyPluginManager = $strategyPluginManager;
    $this->environmentDiscovery = $environmentDiscovery;
  }

  /**
   * Get the strategy plugin.
   *
   * @return string
   *   The strategy plugin ID.
   *
   * @throws \Drupal\va_gov_content_release\Exception\CouldNotDetermineStrategyException
   *   If we could not determine a valid strategy.
   */
  public function getStrategyId() : string {
    $environment = $this->environmentDiscovery->getEnvironment();
    return match (TRUE) {
      $environment->isProduction() => 'github_repository_dispatch',
      $environment->isStaging() => 'github_repository_dispatch',
      $environment->isDev() => 'github_repository_dispatch',
      $environment->isTugboat() => 'local_filesystem_build_file',
      $environment->isLocalDev() => 'local_filesystem_build_file',
      default => throw new CouldNotDetermineStrategyException('Could not determine a valid content release strategy for environment: ' . $environment->getRawValue()),
    };
  }

  /**
   * {@inheritDoc}
   */
  public function triggerContentRelease() : void {
    $strategyId = $this->getStrategyId();
    $this->strategyPluginManager->triggerContentRelease($strategyId);
  }

}
