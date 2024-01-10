<?php

namespace Drupal\va_gov_clp\EventSubscriber;

use Drupal\Core\Entity\EntityTypeInterface;
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
      $nodeEntityType = $entity_types['node'];
      $this->addConstraintsToClp($nodeEntityType);
    }
  }

  /**
   * Adds constraints to Campaign Landing Page Nodes.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type.
   */
  public function addConstraintsToClp(EntityTypeInterface $entityType): void {
    $entityType->addConstraint('RequiredParagraphAB', [
      'toggle' => 'field_clp_faq_panel',
      'readable' => 'Q&A',
      'pluralLabel' => 'Page-Specific or Reusable Q&As',
      'panelLabel' => 'FAQ',
      'fieldParagraphA' => 'field_clp_faq_paragraphs',
      'fieldParagraphB' => 'field_clp_reusable_q_a:field_q_as',
      'requiredErrorDisplayAsMessage' => TRUE,
      'min' => 3,
      'max' => 10,
    ]);
  }

}
