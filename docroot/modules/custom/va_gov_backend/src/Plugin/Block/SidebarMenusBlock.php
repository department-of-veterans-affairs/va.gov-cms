<?php

namespace Drupal\va_gov_backend\Plugin\Block;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkManager;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;
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
  const LOVELL_SECTIONS = [
    '1040' => 'va',
    '1039' => 'tricare',
    '347' => 'both',
  ];

  /**
   * The alias manager interface.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

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
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Array of configuration settings.
   * @param string $plugin_id
   *   The plugin id.
   * @param string $plugin_definition
   *   The plugin definition.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   Core path alias manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Provides an interface for classes representing the result of routing.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Menu\MenuLinkTree $menu_link_tree
   *   Provides access to menu trees.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The http request.
   * @param \Drupal\Core\Menu\MenuLinkManager $menu_link_manager
   *   The menu link manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AliasManagerInterface $alias_manager,
    RouteMatchInterface $route_match,
    EntityTypeManagerInterface $entity_type_manager,
    MenuLinkTree $menu_link_tree,
    RequestStack $request_stack,
    MenuLinkManager $menu_link_manager,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->aliasManager = $alias_manager;
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->menuLinkTree = $menu_link_tree;
    $this->requestStack = $request_stack;
    $this->menuLinkManager = $menu_link_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path_alias.manager'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('menu.link_tree'),
      $container->get('request_stack'),
      $container->get('plugin.manager.menu.link'),
      $container->get('renderer')
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

      // If the current node is Lovell content, filter the menu.
      if ($node->hasField('field_administration')) {
        $section_id = $node->get('field_administration')->target_id;
        if (array_key_exists($section_id, self::LOVELL_SECTIONS)) {
          $this->filterLovellLinks($build['#items'], $section_id, TRUE);
        }
      }
    }
    return [
      '#markup' => $this->renderer->render($build),
    ];

  }

  /**
   * Remove menu items that should not be displayed for a given section id.
   *
   * @param array $menu_items
   *   The array of menu items (links).
   * @param string $section_id
   *   The section id for the current node.
   * @param bool $first_call
   *   Determines if this is the first call of this recursive function.
   */
  protected function filterLovellLinks(array &$menu_items, $section_id, $first_call = FALSE) {
    foreach ($menu_items as $key => &$menu_item) {
      // The menu includes two root menu items: one for VA and one for tricare.
      // These should always be present so we do not filter them.
      if (!$first_call) {
        $id = $menu_item['original_link']->getPluginDefinition()['metadata']['entity_id'];
        $menu_link = $this->entityTypeManager->getStorage('menu_link_content')->load($id);
        $link_section = $menu_link->get('field_menu_section')->getValue()[0]['value'];
        // Compare the section for the page to the section for the menu link.
        if (!$this->isLinkNeeded($section_id, $link_section)) {
          unset($menu_items[$key]);
          continue;
        }
      }

      // Filter any children links for this menu item.
      if (count($menu_item['below'])) {
        $this->filterLovellLinks($menu_item['below'], $section_id);
      }
    }

    // Post filtering - if section is tricare, swap the root items.
    if (($first_call)
    && (self::LOVELL_SECTIONS[$section_id] === 'tricare')
    && (count($menu_items) === 2)) {
      $tricare_menu = array_shift($menu_items);
      $va_menu = array_shift($menu_items);
      $tricare_menu['below'] = $va_menu['below'];
      $va_menu['below'] = [];
      $menu_items = [
        'va_menu' => $va_menu,
        'tricare_menu' => $tricare_menu,
      ];
    }
  }

  /**
   * Determine if a node section and link section are a match.
   *
   * @param string $section_id
   *   The section for a given node.
   * @param string $link_section
   *   The section for a given menu link.
   *
   * @return bool
   *   True if the menu should be displayed, false otherwise.
   */
  protected function isLinkNeeded($section_id, $link_section) {
    $needed = TRUE;
    if ((self::LOVELL_SECTIONS[$section_id] !== $link_section)
    && ($link_section !== 'both')
    && ($section_id !== '347')) {
      $needed = FALSE;
    }
    return $needed;
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

    // Load the node for the path root.
    $url = '/' . $args[1];
    $path = $this->aliasManager->getPathByAlias($url);
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      /** @var \Drupal\node\NodeInterface $node */
      $node_storage = $this->entityTypeManager->getStorage('node');
      $node = $node_storage->load($matches[1]);
      $node_title = $node->getTitle();

      // Lovell is a special case.
      if ($node_title === 'Lovell Federal TRICARE health care') {
        $node_title = 'Lovell Federal VA health care';
      }

      $menu_storage = $this->entityTypeManager->getStorage('menu');
      $menus = $menu_storage->loadMultiple();
      foreach ($menus as $menu) {
        if ($menu->label() === $node_title) {
          $menu_name = $menu->id();
          break;
        }
      }
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
