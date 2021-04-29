<?php

namespace Drupal\va_gov_dashboards\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block plugin definitions for dashboards.
 *
 * @see \Drupal\va_gov_dashboards\Plugin\Block\VcDashboardsBlock
 */
class VcDashboardsBlock extends DeriverBase implements ContainerDeriverInterface {

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
   * Constructor.
   */
  public function __construct() {
    // Only here to satisfy the interface.
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    // Only here to satisfy the interface.
    return new static();
  }

  /**
   * Returns our term page blocks.
   *
   * @return array
   *   The blocks that appear in layout builder.
   */
  private function getBlocks() {
    return [
      'visitor_information' => [
        'title' => 'Visitor information',
        'description' => 'Provide info that helps Veterans prepare for their visit.',
        'action' => 'Go to visitor info',
        'nid' => NULL,
        'anchor' => '#prepare-for-your-visit',
        'image' => 'visitor_information.svg',
      ],
      'services' => [
        'title' => 'Services',
        'description' => 'List the services that Veterans can receive at your facility.',
        'action' => 'Go to services',
        'nid' => NULL,
        'anchor' => '#edit-field-health-services',
        'image' => 'services.svg',
      ],
      'featured_content' => [
        'title' => 'Featured content',
        'description' => 'Highlight up to two Vet center activities, such as events or programs',
        'action' => 'Go to featured content',
        'nid' => NULL,
        'anchor' => '#featured-content',
        'image' => 'featured_content.svg',
      ],
      'operating_status' => [
        'title' => 'Operating status',
        'description' => 'Flag temporary changes to hours and operations for the main location.',
        'action' => 'Update operating status',
        'nid' => NULL,
        'anchor' => '#operating-status',
        'image' => 'operating_status.svg',
      ],
      'vet_center_page' => [
        'title' => 'Main Vet Center page',
        'description' => 'Manage all main location page content.',
        'action' => 'View',
        'nid' => NULL,
        'anchor' => '',
        'image' => 'main_vet_center_page.svg',
      ],
      'locations' => [
        'title' => 'Locations page',
        'description' => 'Manage the page introduction and select nearby locations.',
        'action' => 'View',
        'nid' => NULL,
        'anchor' => '',
        'image' => 'locations_page.svg',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->getBlocks() as $key => $panel) {
      $this->derivatives[$key] = $base_plugin_definition;
      $this->derivatives[$key]['id'] = 'dash_vc_' . $key;
      $this->derivatives[$key]['image'] = $panel['image'];
      $this->derivatives[$key]['admin_label'] = $panel['title'];
      $this->derivatives[$key]['description'] = $panel['description'];
      $this->derivatives[$key]['action'] = $panel['action'];
      $this->derivatives[$key]['nid'] = $panel['nid'];
      $this->derivatives[$key]['anchor'] = $panel['anchor'];
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
