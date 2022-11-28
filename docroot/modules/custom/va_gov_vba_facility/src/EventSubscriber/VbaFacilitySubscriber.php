<?php

namespace Drupal\va_gov_vba_facility\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;

/**
 * VA.gov VBA Facility Event Subscriber.
 */
class VbaFacilitySubscriber implements EventSubscriberInterface {

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
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   *  The renderer.
   */
  private $renderer;

  /**
   * Constructs a EntityEventSubscriber object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The string translation service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string translation service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    TranslationInterface $string_translation,
    UserPermsService $user_perms_service,
    EntityTypeManager $entity_type_manager,
    RendererInterface $renderer
    ) {
    $this->stringTranslation = $string_translation;
    $this->userPermsService = $user_perms_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
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
    if ($event->getDisplay()->getTargetBundle() === 'vba_facility') {
      $build = &$event->getBuild();
      $services = $build['field_vba_services'] ?? [];
      foreach ($services as $key => $service) {
        if (is_numeric($key) && !empty($service['#options'])) {
          $service_node = $service['#options']['entity'];
          // Get the content from the taxonomy term description field.
          $referenced_term_id = $service_node->get('field_service_name_and_descripti')->getValue()['0']['target_id'];
          $entity = $this->entityTypeManager->getStorage('taxonomy_term')->load($referenced_term_id);
          $view_builder = $this->entityTypeManager->getViewBuilder('taxonomy_term');
          // Use the "vba_facility_service" view mode.
          $readonly_content = $view_builder->view($entity, 'vba_facility_service');
          // Add the taxonomy term description to the render array.
          $description = $this->renderer->render($readonly_content);
          // Append the facility-specific service description.
          $description .= $service_node->get('field_body')->value;
          $formatted_markup = new FormattableMarkup($description, []);
          $build['field_vba_services'][$key]['#suffix'] = $formatted_markup;
        }
      }
    }
  }

}
