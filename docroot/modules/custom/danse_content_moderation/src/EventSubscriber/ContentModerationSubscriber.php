<?php

namespace Drupal\danse_content_moderation\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\entity_events\Event\EntityEvent;
use Drupal\entity_events\EventSubscriber\EntityEventUpdateSubscriber;
use Drupal\danse_content_moderation\WorkflowPayload;
use Drupal\danse\PluginManager;

/**
 * Class WorkflowSubscriber.
 */
class ContentModerationSubscriber extends EntityEventUpdateSubscriber {

  /**
   * @var \Drupal\danse_content_moderation\Plugin\Danse\Workflow
   */
  protected $plugin;

  /**
   * Constructs a new WorkflowSubscriber object.
   */
  public function __construct(PluginManager $danse_plugin_manager) {
    $this->plugin = $danse_plugin_manager->createInstance('workflow');
  }


  /**
   * {@inheritDoc}
   */
  public function onEntityUpdate(EntityEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof ContentEntityInterface && $entity->getEntityTypeId() === 'content_moderation_state') {
      $payload = WorkflowPayload::createFromEntity($entity);

      $this->plugin->createWorkflowEvent($payload);
    }
  }
}
