<?php

namespace Drupal\va_gov_dashboards\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteMatchInterface;
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
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Gets the node associated with subject term.
   *
   * @param string $content_type
   *   The bundle.
   * @param string $tid
   *   The term id.
   *
   * @return mixed
   *   Node object or null if empty.
   */
  private function getCorrespondingNode(string $content_type, string $tid) {
    $node = NULL;
    $vc_node_fetch = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'vet_center',
      'field_administration' => $tid,
    ]);
    if (!empty($vc_node_fetch)) {
      $node = reset($vc_node_fetch);
    }
    return $node;
  }

  /**
   * Returns our term page blocks.
   *
   * @param string $node_id
   *   The subject node id.
   *
   * @return array
   *   The blocks that appear in layout builder.
   */
  private function getPanels(string $node_id) {
    return [
      'visitor_information' => [
        'title' => 'Visitor information',
        'description' => 'Provide info that helps Veterans prepare for their visit.',
        'action' => 'Go to visitor info',
        'nid' => $node_id,
        'image' => 'visitor_information.svg',
      ],
      'services' => [
        'title' => 'Services',
        'description' => 'List the services that Veterans can receive at your facility.',
        'action' => 'Go to services',
        'nid' => $node_id,
        'image' => 'services.svg',
      ],
      'featured_content' => [
        'title' => 'Featured content',
        'description' => 'Highlight up to two Vet center activities, such as events or programs',
        'action' => 'Go to featured content',
        'nid' => $node_id,
        'image' => 'featured_content.svg',
      ],
      'operating_status' => [
        'title' => 'Operating status',
        'description' => 'Flag temporary changes to hours and operations for the main location.',
        'action' => 'Update operating status',
        'nid' => $node_id,
        'image' => 'operating_status.svg',
      ],
      'vet_center_page' => [
        'title' => 'Main Vet Center page',
        'description' => 'Manage all main location page content.',
        'action' => 'View',
        'nid' => $node_id,
        'image' => 'main_vet_center_page.svg',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $dash_panels = [];
    if ($this->routeMatch->getRouteName() === 'entity.taxonomy_term.canonical'
        || $this->routeMatch->getRouteName() === 'layout_builder.defaults.taxonomy_term.view') {
      $tid = $this->routeMatch->getRawParameter('taxonomy_term') ? $this->routeMatch->getRawParameter('taxonomy_term') : '';
      $vc_node = $this->getCorrespondingNode('vet_center', $tid);
      if (!empty($vc_node)) {
        $vc_node_id = $vc_node->id();
        $dash_panels = $this->getPanels($vc_node_id);
        $vc_locations_node_fetch = $this->entityTypeManager->getStorage('node')->loadByProperties([
          'type' => 'vet_center_locations_list',
          'field_administration' => $tid,
        ]);
        if (!empty($vc_locations_node_fetch)) {
          $vc_locations_node = reset($vc_locations_node_fetch);
          $vc_locations_node_id = $vc_locations_node->id();
          $dash_panels['locations'] = [
            'title' => 'Locations page',
            'description' => 'Manage the page introduction and select nearby locations.',
            'action' => 'View',
            'nid' => $vc_locations_node_id,
            'image' => 'locations_page.svg',
          ];
        }
        foreach ($dash_panels as $key => $panel) {
          $this->derivatives[$key] = $base_plugin_definition;
          $this->derivatives[$key]['id'] = $key;
          $this->derivatives[$key]['image'] = $panel['image'];
          $this->derivatives[$key]['admin_label'] = $panel['title'];
          $this->derivatives[$key]['description'] = $panel['description'];
          $this->derivatives[$key]['action'] = $panel['action'];
          $this->derivatives[$key]['nid'] = $panel['nid'];
        }
      }
    }

    return $this->derivatives;
  }

}
