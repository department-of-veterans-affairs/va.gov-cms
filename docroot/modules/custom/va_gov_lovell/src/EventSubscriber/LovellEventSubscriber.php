<?php

namespace Drupal\va_gov_lovell\EventSubscriber;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityAccessEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\pathauto\PathautoGenerator;
use Drupal\va_gov_lovell\Event\BreadcrumbPreprocessEvent;
use Drupal\va_gov_lovell\LovellOps;
use Drupal\va_gov_lovell\Variables\BreadcrumbEventVariables;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Lovell Entity Event Subscriber.
 */
class LovellEventSubscriber implements EventSubscriberInterface {

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
      BreadcrumbPreprocessEvent::name() => 'preprocessBreadcrumb',
      EntityHookEvents::ENTITY_ACCESS => 'entityAccess',
      EntityHookEvents::ENTITY_INSERT => 'entityInsert',
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_form.alter' => 'alterRegionalHealthServiceNodeForm',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_edit_form.alter' => 'alterRegionalHealthServiceNodeForm',
    ];
  }

  /**
   * Entity access Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityAccessEvent $event
   *   The event.
   */
  public function entityAccess(EntityAccessEvent $event): void {
    $entity = $event->getEntity();
    $operation = $event->getOperation();
    $account = $event->getAccount();
    $accessResult = $this->restrictLovellSystemToAdmin($entity, $operation, $account);
    $event->setAccessResult($accessResult);
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
   * Alterations to VAMC system health service node forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterRegionalHealthServiceNodeForm(FormIdAlterEvent $event): void {
    $this->winnowServiceNamesForTricare($event);
  }

  /**
   * Adds javascript to winnow available Health Services by system.
   *
   * This is a special case to accommodate Lovell TRICARE.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function winnowServiceNamesForTricare(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_lovell/winnow_service_names_tricare';
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
      if (!array_key_exists($section_id, LovellOps::LOVELL_SECTIONS)) {
        return;
      }
      elseif (LovellOps::isLovellBothListingPage($entity)) {
        return;
      }

      // If this node is in a Lovell section disable pathauto pattern.
      // @phpstan-ignore-next-line
      $entity->path->pathauto = 0;
    }
  }

  /**
   * Restrict non-admins to view only access for Lovell umbrella system.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being accessed.
   * @param string $operation
   *   The type of access operation: view, edit, delete, etc.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account attempting access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  protected function restrictLovellSystemToAdmin(EntityInterface $entity, string $operation, AccountInterface $account) {
    if (($entity instanceof NodeInterface)
    && ($entity->bundle() === 'health_care_region_page')
    && ($entity->hasField('field_administration'))) {
      $user_roles = $account->getRoles();
      $section_id = $entity->get('field_administration')->target_id;
      if (($section_id === LovellOps::BOTH_ID)
      && (!in_array('administrator', $user_roles))
      && ($operation != 'view')) {
        return AccessResult::forbidden();
      }
    }
    return AccessResult::neutral();
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
          if (!empty(LovellOps::LOVELL_SECTIONS[$section_id])) {
            $entity->set('field_menu_section', LovellOps::LOVELL_SECTIONS[$section_id]);
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
      $section_id = $node->get('field_administration')->target_id;
      if ($section_id === LovellOps::TRICARE_ID) {
        $breadcrumb = $this->swapAforB('VA', 'TRICARE', $breadcrumb);
        $breadcrumb = $this->swapAforB('BOTH', 'TRICARE', $breadcrumb);
      }
      else {
        $breadcrumb = $this->swapAforB('BOTH', 'VA', $breadcrumb);
      }

      $variables->set('breadcrumb', $breadcrumb);
    }
  }

  /**
   * Replaces A Links and paths with B.
   *
   * @param string $a
   *   Capped values of either TRICARE, VA, or BOTH.
   * @param string $b
   *   Capped values of either TRICARE, VA, or BOTH.
   * @param array $breadcrumb
   *   AN array of breadcrumbs.
   *
   * @return array
   *   The breadcrumb.
   */
  protected function swapAforB($a, $b, array $breadcrumb) {
    $a_path = constant('\Drupal\va_gov_lovell\LovellOps::' . "{$a}_PATH");
    $a_name = constant('\Drupal\va_gov_lovell\LovellOps::' . "{$a}_NAME");

    $b_path = constant('\Drupal\va_gov_lovell\LovellOps::' . "{$b}_PATH");
    $b_name = constant('\Drupal\va_gov_lovell\LovellOps::' . "{$b}_NAME");
    foreach ($breadcrumb as $key => $link) {
      // Either the system path itself or the system path in the longer path.
      if (strcmp(ltrim($link['url'], "/"), $a_path) === 0
          || str_contains($link['url'], "/$a_path/")) {
        $breadcrumb[$key]['url'] = str_replace($a_path, $b_path, $link['url']);
        if (is_string($link['text'])) {
          $breadcrumb[$key]['text'] = str_replace($a_name, $b_name, $link['text']);
        }
      }
    }
    return $breadcrumb;
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
      if (!array_key_exists($section_id, LovellOps::LOVELL_SECTIONS)) {
        return;
      }
      if ($entity->id() === LovellOps::LOVELL_FEDERAL_SYSTEM_ID  || LovellOps::isLovellBothListingPage($entity)) {
        // Special case of Lovell Federal system,
        // or listing pages that are in both systems but not rendered.
        return;
      }

      // Determine which prefixes are valid based on section.
      $valid_prefixes = LovellOps::getValidPrefixes($section_id);
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
    foreach ($prefixes as $prefix) {
      // Replace the first segment and use the rest.
      $url = "/{$prefix}/" . implode('/', array_slice($url_pieces, 2));
      // Remove trailing -0 from using the menu parent for VAMC detail pages.
      // This also applies to leadership pages.
      $url = preg_replace('/-0$/', '', $url);
      $new_alias = PathAlias::Create([
        'path' => "/node/{$node->id()}",
        'alias' => $url,
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
      'langcode' => $node->language()->getId(),
    ]);

    return $existing_aliases;
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
