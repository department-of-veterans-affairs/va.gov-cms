<?php

namespace Drupal\va_gov_vba_facility\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *  The entity manager.
   */
  private $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The string translation service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    TranslationInterface $string_translation,
    UserPermsService $user_perms_service,
    EntityTypeManagerInterface $entity_type_manager,
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
    $display = $event->getDisplay();
    if (($display->getTargetBundle() === 'vba_facility') && ($display->getOriginalMode() === 'full')) {
      $build = &$event->getBuild();
      $services = $build['field_vba_services'] ?? [];
      foreach ($services as $key => $service) {
        $description = new FormattableMarkup('', []);
        if (is_numeric($key) && !empty($service['#options']['entity'])) {
          $service_node = $service['#options']['entity'];
          $referenced_terms = $service_node->get('field_service_name_and_descripti')->referencedEntities();
          // Render the national service term description (if available).
          if (!empty($referenced_terms)) {
            $referenced_term = reset($referenced_terms);
            if ($referenced_term) {
              $view_builder = $this->entityTypeManager->getViewBuilder('taxonomy_term');
              $referenced_term_content = $view_builder->view($referenced_term, 'vba_facility_service');
              $description = $this->renderer->renderRoot($referenced_term_content);
            }
          }
          else {
            $description = new FormattableMarkup(
              '<div><strong>Notice: The national service description was not found.</strong></div>',
                []);
          }
          // Append the facility-specific service description (no matter what).
          $description .= $service_node->get('field_body')->value;
          $formatted_markup = new FormattableMarkup($description, []);
          $build['field_vba_services'][$key]['#suffix'] = $formatted_markup;
        }
      }
    }
  }

}
