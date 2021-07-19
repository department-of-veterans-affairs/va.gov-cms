<?php

namespace Drupal\va_gov_backend\Plugin\Block;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkManager;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a 'SidebarMenus' block plugin.
 *
 * @Block(
 *   id = "sidebar_menu_block",
 *   admin_label = @Translation("Sidebar Menus block"),
 *   category = @Translation("Menus")
 * )
 */
class SidebarMenusBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route match interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The Menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuLinkTree;

  /**
   * The http request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManager
   */
  protected $menuLinkManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Array of configuration settings.
   * @param string $plugin_id
   *   The plugin id.
   * @param string $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Provides an interface for classes representing the result of routing.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param Drupal\Core\Menu\MenuLinkTree $menu_link_tree
   *   Provides access to menu trees.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The http request.
   * @param Drupal\Core\Menu\MenuLinkManager $menu_link_manager
   *   The menu link manager.
   */
  public function __construct(
  array $configuration,
  $plugin_id,
  $plugin_definition,
  RouteMatchInterface $route_match,
  EntityTypeManagerInterface $entity_type_manager,
  MenuLinkTree $menu_link_tree,
  RequestStack $request_stack,
  MenuLinkManager $menu_link_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->menuLinkTree = $menu_link_tree;
    $this->requestStack = $request_stack;
    $this->menuLinkManager = $menu_link_manager;
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
      $container->get('menu.link_tree'),
      $container->get('request_stack'),
      $container->get('plugin.manager.menu.link')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $node = $this->routeMatch->getParameters()->get('node');
    $menu_name = $this->getMenuName();
    // Before we start doing stuff, make sure we have a node object.
    if ($node instanceof NodeInterface) {

      $route_params = ['node' => $node->id()];
      // Load the menu.
      $menu_links = $this->menuLinkManager->loadLinksByRoute('entity.node.canonical', $route_params, $menu_name);

      // If it has links, put them together.
      $build = $this->getMenuBuild($menu_links);
    }
    return [
      '#markup' => render($build),
    ];

  }

  /**
   * Returns the name of the vamc menu for the current context.
   *
   * @return string
   *   The menu name.
   */
  public function getMenuName() {
    $menu_name = '';
    // This is the only thing we have to find the menu name.
    $path = $this->requestStack->getCurrentRequest()->getPathInfo();
    $args = explode('/', $path);
    // Menu machine name and pathname usually differ by "va-" prefix.
    $menu_name = substr($args[1], 0, 3) === 'va-' ? $args[1] : 'va-' . $args[1];
    // Pittsburgh is a snowflake.
    if ($args[1] === 'pittsburgh-health-care') {
      $menu_name = $args[1];
    }
    return $menu_name;
  }

  /**
   * Returns the rendered vamc menu for the current context.
   *
   * @return array
   *   Returns the rendered menu array, or empty array if no menu.
   */
  protected function getMenuBuild($menu_links) {
    if (empty($menu_links)) {
      return [];
    }
    $menu_parameters = new MenuTreeParameters();
    // This is needed to sort by weights set in ui.
    $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    // If we don't do this, we won't see the whole menu on nested pages.
    $menu_parameters->setRoot('');
    $menu_parameters->onlyEnabledLinks();
    $tree = $this->menuLinkTree->load($this->getMenuName(), $menu_parameters);
    $tree = $this->menuLinkTree->transform($tree, $manipulators);
    return $this->menuLinkTree->build($tree);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
