<?php

namespace Drupal\va_gov_tour\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\core_event_dispatcher\Event\Entity\EntityOperationEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

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
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user perms service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string entity type service.
   */
  public function __construct(
    TranslationInterface $string_translation,
    AccountInterface $current_user,
    EntityTypeManager $entity_type_manager
  ) {
    $this->stringTranslation = $string_translation;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_OPERATION => 'entityOperation',
    ];
  }

  /**
   * Alterations to entity operations.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityOperationEvent $event
   *   The entity operation event.
   */
  public function entityOperation(EntityOperationEvent $event) {
    $operations = $event->getOperations();
    $entity = $event->getEntity();
    $info = $event->getEntity()->getEntityType();

    // Add manage tour link if this entity type is the bundle of
    // another and that type has field UI enabled.
    if (($bundle_of = $info->getBundleOf()) && $this->entityTypeManager->getDefinition($bundle_of)->get('field_ui_base_route')) {

      if ($this->currentUser->hasPermission('administer tour')) {
        $operations['manage-ct-tour'] = [
          'title' => $this->t('Manage tour'),
          'weight' => 28,
          'url' => Url::fromRoute("va_gov_tour.tour_form", [
            $entity->getEntityTypeId() => $entity->id(),
          ]),
        ];
      }
    }
    return $operations;
  }

}
