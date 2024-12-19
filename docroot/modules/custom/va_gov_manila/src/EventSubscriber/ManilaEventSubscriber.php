<?php

namespace Drupal\va_gov_manila\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityBundleFieldInfoAlterEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\node\NodeInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\pathauto\PathautoGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Manila Entity Event Subscriber.
 */
class ManilaEventSubscriber implements EventSubscriberInterface {

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
   * The Manila VA system Section id.
   *
   * @var int
   */
  protected $manilaVaSystemId = '1187';

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
    MessengerInterface $messenger,
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
      EntityHookEvents::ENTITY_INSERT => 'entityInsert',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
      EntityHookEvents::ENTITY_BUNDLE_FIELD_INFO_ALTER => 'alterFieldInfo',
    ];
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
   * Add validation to listing and office fields (for Manila).
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityBundleFieldInfoAlterEvent $event
   *   The event.
   */
  public function alterFieldInfo(EntityBundleFieldInfoAlterEvent $event): void {
    $list_item_bundles = [
      'event',
      'news_story',
      'press_release',
    ];
    $entity_type = $event->getEntityType();
    $bundle = $event->getBundle();
    $fields = $event->getFields();
    if (($entity_type->id() === 'node')
    && (in_array($bundle, $list_item_bundles))
    && (isset($fields['field_listing']))
    && (isset($fields['field_administration']))) {
      // Limit the listing selection based on section.
      $fields['field_listing']->addConstraint('ManilaSectionListParity');
    }
    $office_bundles = [
      'person_profile',
    ];
    if (($entity_type->id() === 'node')
    && (in_array($bundle, $office_bundles))
    && (isset($fields['field_office']))
    && (isset($fields['field_administration']))) {
      // Limit the office selection based on section.
      $fields['field_office']->addConstraint('ManilaSectionListParity');
    }
  }

  /**
   * Update path aliases for Manila content.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function updatePathAliases(EntityInterface $entity): void {
    if (($entity instanceof NodeInterface)
    && ($entity->isDefaultRevision())
    && ($entity->hasField('path'))
    && ($entity->hasField('field_administration'))) {
      // If this content does not belong to a Manila section do nothing.
      $section_id = $entity->get('field_administration')->target_id;
      if ($section_id !== $this->manilaVaSystemId) {
        return;
      }

      // Determine which prefixes are valid based on section.
      $manila_prefix = [$this->manilaVaSystemId => 'manila-va-clinic'];
      $valid_aliases = $this->generateValidAliases($manila_prefix, $entity);

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
    // If this is a Manila VA Clinic node use a specific alias.
    $bundle_type = $node->bundle();
    if ($bundle_type === 'health_care_local_facility') {
      $new_alias = PathAlias::Create([
        'path' => "/node/{$node->id()}",
        'alias' => '/manila-va-clinic',
        'langcode' => $node->language()->getId(),
      ]);
      $new_aliases = [$new_alias];
    }
    else {
      // Use the Pathauto module alias pattern for all other content types.
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
    }
    return $new_aliases;
  }

  /**
   * Generate valid Manila url prefixes for the provided section.
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
