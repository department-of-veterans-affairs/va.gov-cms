<?php

namespace Drupal\va_gov_lovell\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\pathauto\PathautoGenerator;
use Drupal\va_gov_lovell\Event\BreadcrumbPreprocessEvent;
use Drupal\va_gov_lovell\Event\MenuPreprocessEvent;
use Drupal\va_gov_lovell\Variables\BreadcrumbEventVariables;
use Drupal\va_gov_lovell\Variables\MenuEventVariables;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Lovell Entity Event Subscriber.
 */
class LovellEventSubscriber implements EventSubscriberInterface {
  const LOVELL_SECTIONS = [
    '1040' => 'va',
    '1039' => 'tricare',
    '347' => 'both',
  ];

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * The route match interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The pathauto generator.
   *
   * @var \Drupal\pathauto\PathautoGenerator
   *  The pathauto generator.
   */
  private $pathautoGenerator;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The string entity type service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Provides an interface for classes representing the result of routing.
   * @param \Drupal\pathauto\PathautoGenerator $pathautoGenerator
   *   The pathauto generator service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    EntityTypeManager $entityTypeManager,
    RouteMatchInterface $route_match,
    PathautoGenerator $pathautoGenerator,
    MessengerInterface $messenger
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $route_match;
    $this->pathautoGenerator = $pathautoGenerator;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
      EntityHookEvents::ENTITY_INSERT => 'entityInsert',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
      BreadcrumbPreprocessEvent::name() => 'preprocessBreadcrumb',
      MenuPreprocessEvent::name() => 'preprocessMenu',
    ];
  }

  /**
   * Entity presave Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    $this->setMenuSection($entity);
    $this->blockLovellPathauto($entity);
  }

  /**
   * Entity insert Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();
    $this->updatePathAliases($entity);
  }

  /**
   * Entity update Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    $this->updatePathAliases($entity);
  }

  /**
   * Breadcrumb preprocess Event call.
   *
   * @param \Drupal\va_gov_lovell\Event\BreadcrumbPreprocessEvent $event
   *   The event.
   */
  public function preprocessBreadcrumb(BreadcrumbPreprocessEvent $event): void {
    $variables = $event->getVariables();
    $this->updateLovellBreadcrumbs($variables);
  }

  /**
   * Menu preprocess Event call.
   *
   * @param \Drupal\va_gov_lovell\Event\MenuPreprocessEvent $event
   *   The event.
   */
  public function preprocessMenu(MenuPreprocessEvent $event): void {
    // @todo can we make this work.
    // $variables = $event->getVariables();
    // $this->updateLovellMenu($variables);
  }

  /**
   * Disable pathauto for Lovell nodes.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function blockLovellPathauto(EntityInterface $entity): void {
    if (($entity instanceof NodeInterface)
    && ($entity->hasField('path'))
    && ($entity->hasField('field_administration'))) {
      $section_id = $entity->get('field_administration')->target_id;
      if (!array_key_exists($section_id, self::LOVELL_SECTIONS)) {
        return;
      }
      // If this node is in a Lovell section disable pathauto pattern.
      // @phpstan-ignore-next-line
      $entity->path->pathauto = 0;
    }
  }

  /**
   * Set field_menu_section for Lovell menu items.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function setMenuSection(EntityInterface $entity): void {
    if (($entity instanceof MenuLinkContentInterface)
    && ($entity->getMenuName() === 'lovell-federal-health-care')
    && ($entity->hasfield('field_menu_section'))) {
      // Load the node entity for this menu item.
      $url_object = $entity->getUrlObject();
      $params = $url_object->getRouteParameters();
      if (array_key_exists('node', $params)) {
        $nid = $params['node'];
        $node_storage = $this->entityTypeManager->getStorage('node');
        $node = $node_storage->load($nid);
        /** @var \Drupal\node\NodeInterface $node*/
        if ($node->hasField('field_administration')) {
          $section_id = $node->get('field_administration')->target_id;
          // Set section for this menu item.
          if (!empty(self::LOVELL_SECTIONS[$section_id])) {
            $entity->set('field_menu_section', self::LOVELL_SECTIONS[$section_id]);
          }
        }
      }
    }
  }

  /**
   * Update breadcrumbs for Lovell content.
   *
   * @param \Drupal\va_gov_lovell\Variables\BreadcrumbEventVariables $variables
   *   BreadcrumbEventVariables.
   */
  protected function updateLovellBreadcrumbs(BreadcrumbEventVariables $variables): void {
    $breadcrumb = $variables->getBreadcrumb();
    $node = $this->routeMatch->getParameter('node');
    if (($node instanceof NodeInterface)
    && ($node->hasField('path'))
    && ($node->hasField('field_administration'))) {
      // If this content does not belong to Lovell Tricare do nothing.
      $section_id = $node->get('field_administration')->target_id;
      if ($section_id !== '1039') {
        return;
      }

      foreach ($breadcrumb as $key => $link) {
        if (str_contains($link['url'], 'lovell-federal-va-health-care')) {
          $breadcrumb[$key]['url'] = str_replace('lovell-federal-va-health-care', 'lovell-federal-tricare-health-care', $link['url']);
          if (is_string($link['text'])) {
            $breadcrumb[$key]['text'] = str_replace('Lovell Federal VA health care', 'Lovell Federal Tricare health care', $link['text']);
          }
        }
      }

      $variables->set('breadcrumb', $breadcrumb);
    }
  }

  /**
   * Update menu for Lovell content.
   *
   * @param \Drupal\va_gov_lovell\Variables\MenuEventVariables $variables
   *   MenuEventVariables.
   */
  protected function updateLovellMenu(MenuEventVariables &$variables): void {
    $menu = &$variables->getMenu();
    $node = $this->routeMatch->getParameter('node');
    if (($node instanceof NodeInterface)
    && ($node->hasField('path'))
    && ($node->hasField('field_administration'))) {
      // If this content does not belong to a Lovell section do nothing.
      $section_id = $node->get('field_administration')->target_id;
      if (!array_key_exists($section_id, self::LOVELL_SECTIONS)) {
        return;
      }

      // If the current node is Lovell content, filter the menu.
      $this->filterLovellLinks($menu, $section_id, TRUE);
      $variables->set('items', $menu);
    }
  }

  /**
   * Remove menu items that should not be displayed for a given section id.
   *
   * @param array $menu_links
   *   The array of menu links.
   * @param string $section_id
   *   The section id for the current node.
   * @param bool $first_call
   *   Determines if this is the first call of this recursive function.
   */
  protected function filterLovellLinks(array &$menu_links, $section_id, $first_call = FALSE) {
    foreach ($menu_links as $key => &$menu_link) {
      // The menu includes two root menu items: one for VA and one for tricare.
      // These should always be present so we do not filter them.
      if (!$first_call) {
        // Compare the section for the menu link to the section for the page.
        $link_section = $menu_link['entity']->get('field_menu_section')->getValue()[0]['value'];
        if (!$this->isLinkNeeded($section_id, $link_section)) {
          unset($menu_links[$key]);
          continue;
        }
      }

      // Filter any children links for this menu item.
      if (count($menu_link['below'])) {
        $this->filterLovellLinks($menu_link['below'], $section_id);
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
   * Update path aliases for Lovell content.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function updatePathAliases(EntityInterface $entity): void {
    if (($entity instanceof NodeInterface)
    && ($entity->isDefaultRevision())
    && ($entity->hasField('path'))
    && ($entity->hasField('field_administration'))) {
      // If this content does not belong to a Lovell section do nothing.
      $section_id = $entity->get('field_administration')->target_id;
      if (!array_key_exists($section_id, self::LOVELL_SECTIONS)) {
        return;
      }

      // Determine which prefixes are valid based on section.
      $valid_prefixes = $this->getValidPrefixes($section_id);
      $valid_aliases = $this->generateValidAliases($valid_prefixes, $entity);

      // Get any existing aliases for this node.
      $existing_aliases = $this->getExistingNodeAliases($entity);

      // Omit existing aliases with a valid match from further processing.
      $this->omitAliasMatches($valid_aliases, $existing_aliases);

      // Commit any remaining valid aliases to the database.
      foreach ($valid_aliases as $key => $valid_alias) {
        $valid_alias->save();
        unset($valid_aliases[$key]);
      }

      // Remove unused existing aliases.
      foreach ($existing_aliases as $existing_alias) {
        $existing_alias->delete();
      }
    }
  }

  /**
   * Generate an array of valid Pathalias objects for this node.
   *
   * @param array $prefixes
   *   An array of prefix strings.
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return array
   *   An array of Pathalias objects.
   */
  protected function generateValidAliases(array $prefixes, NodeInterface $node): array {
    $new_url = $this->pathautoGenerator->createEntityAlias($node, 'return');
    $url_pieces = explode('/', $new_url);
    $new_aliases = [];
    foreach ($prefixes as $section => $prefix) {
      $new_alias = PathAlias::Create([
        'path' => '/node/' . $node->id(),
        'alias' => '/' . $prefix . '/' . implode('/', array_slice($url_pieces, 2)),
        'langcode' => $node->language()->getId(),
      ]);
      $new_aliases[] = $new_alias;
    }
    return $new_aliases;
  }

  /**
   * Generate valid lovell url prefixes for the provided section.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return array
   *   An array of existing Pathalias objects for this node.
   */
  protected function getExistingNodeAliases(NodeInterface $node): array {
    // Retrieve existing aliases for this node.
    $path = '/node/' . $node->id();
    $path_alias_manager = $this->entityTypeManager->getStorage('path_alias');
    $existing_aliases = $path_alias_manager->loadByProperties([
      'path'     => $path,
      'langcode' => 'en',
    ]);

    return $existing_aliases;
  }

  /**
   * Generate valid lovell url prefixes for the provided section.
   *
   * @param string $section_id
   *   An id for a section taxonomy term.
   *
   * @return array
   *   An array of valid url prefixes .
   */
  protected function getValidPrefixes(string $section_id): array {
    // Define valid url prefixes for Lovell content.
    $valid_prefixes = [
      '1039' => 'lovell-federal-tricare-health-care',
      '1040' => 'lovell-federal-va-health-care',
    ];

    // If section is not both remove invalid prefixes.
    if ($section_id !== '347') {
      $valid_prefixes = array_intersect_key($valid_prefixes, [$section_id => 'keep']);
    }

    return $valid_prefixes;
  }

  /**
   * Omit aliases common to the supplied arrays.
   *
   * @param array $valid_aliases
   *   An array of aliases a node should have.
   * @param array $existing_aliases
   *   An array of aliases a node currently has.
   */
  protected function omitAliasMatches(array &$valid_aliases, array &$existing_aliases): void {
    if (count($valid_aliases) && count($existing_aliases)) {
      foreach ($valid_aliases as $id => $valid_alias) {
        foreach ($existing_aliases as $section => $existing_alias) {
          if ($valid_alias->alias->value === $existing_alias->alias->value) {
            // Remove matched elements.
            unset($valid_aliases[$id]);
            unset($existing_aliases[$section]);
            break;
          }
        }
      }
    }
  }

}
