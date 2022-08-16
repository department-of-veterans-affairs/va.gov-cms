<?php

namespace Drupal\va_gov_lovell\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\menu_link_content\MenuLinkContentInterface;
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
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    EntityTypeManager $entityTypeManager,
    MessengerInterface $messenger
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
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
          $section_map = [
            '1040' => 'va',
            '1039' => 'tricare',
            '347' => 'both',
          ];

          // Set section for this menu item.
          if (!empty($section_map[$section_id])) {
            $entity->set('field_menu_section', $section_map[$section_id]);
          }
        }
      }
    }
  }

}
