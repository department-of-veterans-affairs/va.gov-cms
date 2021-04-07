<?php

namespace Drupal\va_gov_dashboards\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'VcDashboardsBlock' block plugin.
 *
 * @Block(
 *   id = "va_gov_dashboards",
 *   admin_label = @Translation("VC Dashboards block"),
 *   deriver = "Drupal\va_gov_dashboards\Plugin\Derivative\VcDashboardsBlock"
 * )
 */
class VcDashboardsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block_defintion = $this->getPluginDefinition();
    $id = $this->getDerivativeId();
    $build = [];
    $build = [
      '#theme' => 'vc_dashboards_block',
      '#attributes' => [
        'class' => ['dashboards-block'],
        'id' => $id,
      ],
      '#attached' => [
        'library' => [
          'va_gov_dashboards/dashboard_blocks',
        ],
      ],
      '#id' => $block_defintion['id'],
      '#image' => $block_defintion['image'],
      '#title' => $block_defintion['admin_label'],
      '#description' => $block_defintion['description'],
      '#action' => $block_defintion['action'],
      '#nid' => $block_defintion['nid'],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['taxonomy_term:' . $this->routeMatch->getRawParameter('taxonomy_term')];
  }

}
