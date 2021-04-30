<?php

namespace Drupal\va_gov_dashboards\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\va_gov_dashboards\Service\VetCenterDashboard;
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
   * Dashboard service providing related tids and nids.
   *
   * @var \Drupal\va_gov_dashboards\Service\VetCenterDashboard
   */
  protected $dashboard;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Array of configuration settings.
   * @param string $plugin_id
   *   The plugin id.
   * @param string $plugin_definition
   *   The plugin definition.
   * @param \Drupal\va_gov_dashboards\Service\VetCenterDashboard $dashboard
   *   Dashboard service providing related tids and nids.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VetCenterDashboard $dashboard) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dashboard = $dashboard;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('va_gov_dashboards.vetcenter'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block_definition = $this->getPluginDefinition();
    $nid = ($block_definition['id'] === 'dash_vc_locations') ? $this->dashboard->getLocationNid() : $this->dashboard->getFacilityNid();
    $build = [];
    if ($nid) {
      $build = [
        '#theme' => 'vc_dashboards_block',
        '#attributes' => [
          'class' => ['dashboards-block'],
          'id' => $this->getDerivativeId(),
        ],
        '#attached' => [
          'library' => [
            'va_gov_dashboards/dashboard_blocks',
          ],
        ],
        '#id' => $block_definition['id'],
        '#image' => $block_definition['image'],
        '#title' => $block_definition['admin_label'],
        '#description' => $block_definition['description'],
        '#action' => $block_definition['action'],
        '#nid' => $nid,
        '#anchor' => $block_definition['anchor'],
        '#cache' => [
          'tags' => ['route'],
          'contexts' => ['route'],
          'max-age' => Cache::PERMANENT,
        ],
      ];
    }

    return $build;
  }

}
