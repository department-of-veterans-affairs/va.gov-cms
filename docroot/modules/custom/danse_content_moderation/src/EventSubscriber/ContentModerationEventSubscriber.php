<?php

namespace Drupal\danse_content_moderation\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\danse_content_moderation\WorkflowPayload;
use Drupal\danse\PluginManager;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class WorkflowSubscriber.
 */
class ContentModerationEventSubscriber implements EventSubscriberInterface {

  /**
   * The DANSE Content Moderation plugin.
   *
   * @var \Drupal\danse_content_moderation\Plugin\Danse\ContentModeration
   */
  protected $plugin;

  /**
   * Constructs a new WorkflowSubscriber object.
   */
  public function __construct(PluginManager $danse_plugin_manager) {
    $this->plugin = $danse_plugin_manager->createInstance('content_moderation');
  }

  /**
   * React to entity updates.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function onEntityUpdate(EntityUpdateEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof ContentEntityInterface && $entity->getEntityTypeId() === 'content_moderation_state') {
      $payload = WorkflowPayload::createFromEntity($entity);

      $this->plugin->createWorkflowEvent($payload);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_UPDATE => 'onEntityUpdate',
    ];
  }

}
