<?php

namespace Drupal\va_gov_facilities\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VA Facilities Entity Event Subscriber.
 */
class FacilitiesSubscriber implements EventSubscriberInterface {

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
    $this->clearNormalStatusDetails($entity);
  }

  /**
   * Clear status details when operating status is normal.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function clearNormalStatusDetails(EntityInterface $entity): void {
    if ($entity instanceof NodeInterface) {
      $facilitiesWithStatus = [
        'health_care_local_facility',
        'nca_facility',
        'vba_facility',
        'vet_center',
        'vet_center_outstation',
      ];
      $bundle = $entity->bundle();
      /** @var \Drupal\node\NodeInterface $entity */
      if (in_array($entity->bundle(), $facilitiesWithStatus)
      && ($entity->hasField('field_operating_status_facility'))
      && ($entity->hasField('field_operating_status_more_info'))) {
        $status = $entity->get('field_operating_status_facility')->value;
        $details = $entity->get('field_operating_status_more_info')->value;
        if ($status === 'normal' && !empty($details)) {
          $entity->set('field_operating_status_more_info', '');
        }
      }
    }
  }

}
