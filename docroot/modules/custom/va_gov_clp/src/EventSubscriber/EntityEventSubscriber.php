<?php

namespace Drupal\va_gov_clp\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityTypeAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Campaign Landing Page Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_TYPE_ALTER => 'entityTypeAlter',
    ];
  }

  /**
   * Equivalent of hook_entity_type_alter().
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityTypeAlterEvent $event
   *   The event for entityTypeAlter.
   */
  public function entityTypeAlter(EntityTypeAlterEvent $event): void {
    $entity_types = $event->getEntityTypes();
    if (!empty($entity_types['node'])) {
      $entity = $entity_types['node'];
      $entity->addConstraint('RequiredParagraphAB', [
        'toggle' => 'field_clp_faq_panel',
        'readable' => 'Q&A',
        'fieldParagraphA' => 'field_clp_faq_paragraphs',
        'fieldParagraphB' => 'field_clp_reusable_q_a:field_q_as',
        'min' => 3,
        'max' => 10,
      ]);
    }
  }

}
