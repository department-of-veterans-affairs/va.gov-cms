<?php

namespace Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\va_gov_content_release\Annotation\EntityEventStrategy;
use Drupal\va_gov_content_release\Exception\UnknownStrategyException;
use Drupal\va_gov_content_types\Entity\VaNodeInterface;

/**
 * Manages the Entity Event Strategy plugins.
 */
class StrategyPluginManager extends DefaultPluginManager implements StrategyPluginManagerInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Plugin/EntityEventStrategy',
      $namespaces,
      $module_handler,
      StrategyPluginInterface::class,
      EntityEventStrategy::class
    );
    $this->alterInfo('va_gov_content_release_entity_event_strategy');
    $this->setCacheBackend($cache_backend, 'va_gov_content_release_entity_event_strategy');
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
  public function shouldTriggerContentRelease(string $id, VaNodeInterface $node) : bool {
    return $this->getStrategy($id)->shouldTriggerContentRelease($node);
  }

}
