<?php

namespace Drupal\va_gov_vba_facility\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;


/**
 * VA.gov VBA facility Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * UserPerms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   *  The entity field manager.
   */
  private $entityFieldManager;

  /**
   * Constructs a EntityEventSubscriber object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The string translation service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string translation service.
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field service.
   */
  public function __construct(
    TranslationInterface $string_translation,
    UserPermsService $user_perms_service,
    EntityTypeManager $entity_type_manager
    ) {
    $this->stringTranslation = $string_translation;
    $this->userPermsService = $user_perms_service;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_VIEW_ALTER => 'entityViewAlter',
    ];
  }

   /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event):void {
    $this->appendServiceTermDescriptionToVbaFacility($event);
  }



  /**
   * Appends VBA facility service description to title on facility node:view.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function appendServiceTermDescriptionToVbaFacility(EntityViewAlterEvent $event):void {
    // dd($event);
    if ($event->getDisplay()->getTargetBundle() === 'vba_facility') {
      $build = &$event->getBuild();
      $services = $build['field_vba_services'] ?? [];
      foreach ($services as $key => $service) {
        if (is_numeric($key) && !empty($service['#options'])) {
          $service_node = $service['#options']['entity'];

          // Look for real content in field_body. If just line breaks
          // and empty tags use field_service_name_and_descripti.
          $field_body = $service_node->get('field_body')->value;

          $field_vba_service_description =
          '<div class="vba-services-term-description field-group-tooltip tooltip-layout centralized css-tooltip not-empty-display-block">' .
            trim($service_node->get('field_service_name_and_descripti')
            ->entity->get('field_vba_service_descrip')->value) .
            '</div>';
          $body_tags_removed = trim(strip_tags($field_body));
          $body_tags_and_ws_removed = str_replace("\r\n", "", $body_tags_removed);
          $description = strlen($body_tags_and_ws_removed) > 15
          ? '<br />' . $field_vba_service_description . trim($field_body)
          : '<br />' .  trim($field_vba_service_description);

          $formatted_markup = new FormattableMarkup($description, []);
          $build['field_vba_services'][$key]['#suffix'] = $formatted_markup;
        }
      }
    }
  }

}
