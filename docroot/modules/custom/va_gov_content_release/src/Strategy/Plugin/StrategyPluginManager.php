<?php

namespace Drupal\va_gov_content_release\Strategy\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\va_gov_content_release\Annotation\ContentReleaseStrategy;
use Drupal\va_gov_content_release\Exception\UnknownStrategyException;

/**
 * Manages the Strategy plugins.
 */
class StrategyPluginManager extends DefaultPluginManager implements StrategyPluginManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Plugin/Strategy',
      $namespaces,
      $module_handler,
      StrategyPluginInterface::class,
      ContentReleaseStrategy::class
    );
    $this->alterInfo('va_gov_content_release_strategy');
    $this->setCacheBackend($cache_backend, 'va_gov_content_release_strategy');
  }

  /**
   * {@inheritDoc}
   */
  public function getStrategy(string $id) : StrategyPluginInterface {
    try {
      return $this->createInstance($id);
    }
    catch (PluginException) {
      throw new UnknownStrategyException("Unknown strategy: $id");
    }
  }

  /**
   * {@inheritDoc}
   */
  public function triggerContentRelease(string $id) : void {
    $this->getStrategy($id)->triggerContentRelease();
  }

}
