<?php

namespace Drupal\va_gov_clone\CloneEntityFinder;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Clone handler plugins.
 */
abstract class CloneEntityFinderBase extends PluginBase implements CloneEntityFinderInterface, ContainerFactoryPluginInterface {

  /**
   * Query engine for nodes.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Generic entity loader.
   *
   * @param array $ids
   *   Array of entity ids.
   * @param string $entity_type
   *   The entity type to load.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The array of \Drupal\Core\Entity\EntityInterface.
   */
  protected function loadEntities(array $ids, string $entity_type) : array {
    return $this->entityTypeManager
      ->getStorage($entity_type)
      ->loadMultiple($ids);
  }

}
