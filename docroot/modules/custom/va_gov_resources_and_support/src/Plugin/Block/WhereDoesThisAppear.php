<?php

namespace Drupal\va_gov_resources_and_support\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'WhereDoesThisAppear' block.
 *
 * @Block(
 *  id = "where_does_this_appear",
 *  admin_label = @Translation("Where does this appear"),
 * )
 */
class WhereDoesThisAppear extends BlockBase implements ContainerFactoryPluginInterface {

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
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager
  ) {
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $links = $this->getPlacements();
    if (!$links) {
      return;
    }
    $block = [];
    $block = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->configuration['label'],
      '#prefix' => '<div id="names-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#cache' => [
        'tags' => ['paragraph_list:q_a_group'],
      ],
      'links' => [
        '#prefix' => $this->t('Before making significant changes, you may want to consider how it might affect other content.'),
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#attributes' => ['class' => 'qa-list'],
        '#items' => $links,
      ],
    ];

    return $block;
  }

  /**
   * Get the node from context if available.
   *
   * @return mixed
   *   Node if available, NULL if not.
   */
  private function getNode() {
    // Drupal sometimes hands us a nid and sometimes an upcasted node object.
    // @TODO remove type checks when the patch at
    // https://www.drupal.org/project/drupal/issues/2730631
    // is committed. (Should be in 9.2)
    $route_parameter = $this->routeMatch->getParameter('node');
    if ($route_parameter instanceof NodeInterface) {
      return $route_parameter;
    }
    elseif (is_numeric($route_parameter)) {
      return $this->entityTypeManager
        ->getStorage('node')
        ->load($route_parameter);
    }
    return NULL;
  }

  /**
   * Get the nodes where item appears.
   *
   * @return array
   *   Links to the nodes where the qa item appears.
   */
  public function getPlacements() {
    $node = $this->getNode();
    if (!$node) {
      return NULL;
    }

    $nid = $this->getNode()->id();
    $qa_query = $this->entityTypeManager->getStorage('paragraph')->getQuery()
      ->condition('field_q_as', [$nid], 'IN');
    $qa_entities = $qa_query->execute();
    if (!$qa_entities) {
      return NULL;
    }

    $node_query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('field_q_a_groups', $qa_entities, 'IN');
    $nids = $node_query->execute();
    if (!$nids) {
      return NULL;
    }

    $loaded_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $links = [];

    foreach ($loaded_nodes as $loaded_node) {
      $links[] = $loaded_node->toLink();
    }

    return $links;
  }

}
