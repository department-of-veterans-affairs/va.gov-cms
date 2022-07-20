<?php

namespace Drupal\va_gov_facilities\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VA Facilities Entity Event Subscriber.
 */
class FacilitiesSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * The active user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   *  The user object.
   */
  private $currentUser;

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
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The string entity type service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    AccountInterface $currentUser,
    EntityTypeManager $entityTypeManager,
    MessengerInterface $messenger
  ) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
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
   * Entity update Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    $this->archiveRelatedHealthFacilityContent($entity);
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

  /**
   * Archive related content when a facility has been archived.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function archiveRelatedHealthFacilityContent(EntityInterface $entity): void {
    if ($entity instanceof NodeInterface && $entity->bundle() === 'health_care_local_facility') {
      // When a facility is archived auto archive related services and events.
      if (isset($entity->original) && ($entity->original instanceof NodeInterface) && $this->isBeingArchived($entity)) {
        $facilityID = $entity->id();
        $relatedTypes = [
          'event',
          'health_care_local_health_service',
          'vha_facility_nonclinical_service',
        ];

        // Find all related published services and events.
        $nodeStorage = $this->entityTypeManager->getStorage('node');
        $query = $nodeStorage->getQuery();
        $query->condition('type', $relatedTypes, 'IN')
          ->condition('field_facility_location', $facilityID)
          ->condition('moderation_state', 'archived', '!=');
        $nids = $query->execute();

        if (count($nids)) {
          $nodes = $nodeStorage->loadMultiple($nids);
          $updated = [
            'event' => 0,
            'health_care_local_health_service' => 0,
            'vha_facility_nonclinical_service' => 0,
          ];
          foreach ($nodes as $node) {
            if ($node instanceof NodeInterface) {
              $this->archiveNode($node);
              $updated[$node->bundle()]++;
            }
          }

          // Status message so editor knows the extent of the changes.
          $message = $this->t('%services health services, %nonclinicals non-clinical services, and %events events were also archived.', [
            '%services' => $updated['health_care_local_health_service'],
            '%nonclinicals' => $updated['vha_facility_nonclinical_service'],
            '%events' => $updated['event'],
          ]);
          $this->messenger->addStatus($message);
        }

      }
    }
  }

  /**
   * Check to see if a supplied node is being archived.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return bool
   *   Is this node being archived?
   */
  protected function isBeingArchived(NodeInterface $node): bool {
    $currentState = $node->get('moderation_state')->value;
    $oldState = $node->original->get('moderation_state')->value;
    if ($oldState === 'archived' || $currentState !== 'archived') {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Archive a supplied node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   */
  protected function archiveNode(NodeInterface $node): void {
    $node->setNewRevision(TRUE);
    $node->set('moderation_state', 'archived');
    $node->setUnpublished();
    $node->setRevisionUserId($this->currentUser->id());
    $node->setChangedTime(time());
    $node->isDefaultRevision(TRUE);
    $node->setRevisionCreationTime(time());
    $node->setRevisionLogMessage('Automatically archived when parent facility was archived.');
    $node->save();
  }

}
