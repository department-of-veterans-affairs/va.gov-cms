<?php

namespace Drupal\va_gov_vba_facility\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityTypeAlterEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\node\NodeInterface;
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
      'hook_event_dispatcher.form_node_vba_facility_edit_form.alter' => 'alterVbaFacilityNodeForm',
      'hook_event_dispatcher.form_node_vba_facility_form.alter' => 'alterVbaFacilityNodeForm',
      EntityHookEvents::ENTITY_TYPE_ALTER => 'entityTypeAlter',
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
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
      $entity->addConstraint('VbaFacilityRequiredFieldsConstraint');
    }
  }

  /**
   * Entity presave Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->clearBannerFields($entity);
    }
  }

  /**
   * Clear status details when banner is not enabled.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function clearBannerFields(EntityInterface $entity): void {
    /** @var \Drupal\node\NodeInterface $entity */
    if ($entity->bundle() === "vba_facility") {
      if ($entity->hasField('field_vba_banner_panel')
      && $entity->field_vba_banner_panel->value == FALSE) {
        $entity->field_banner_title->value = '';
        $entity->field_banner_content->value = '';
      }
    }
  }

  /**
   * Form alterations for VBA facility content type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVbaFacilityNodeForm(FormIdAlterEvent $event): void {
    $this->addStateManagementToBannerFields($event);
    $this->changeBannerTypeNone($event);
  }

  /**
   * Changes test of default select list option for Banner Type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  protected function changeBannerTypeNone(FormIdAlterEvent $event) {
    // Add the '- Select a value -' option to replace '- None -'.
    $form = &$event->getForm();
    if (isset($form['field_alert_type']['widget']['#options']) && array_key_exists('_none', $form['field_alert_type']['widget']['#options'])) {
      $form['field_alert_type']['widget']['#options'] = ['_none' => '- Select a value -'] + $form['field_alert_type']['widget']['#options'];
    }
  }

  /**
   * Add states management to banner fields, based on bool.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function addStateManagementToBannerFields(FormIdAlterEvent $event) {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_vba_facility/set_banner_content_to_required';
    $selector = ':input[name="field_vba_banner_panel[value]"]';

    // Show the banner fields when show banner is checked.
    $form['field_alert_type']['widget']['#states'] = [
      'required' => [
        [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    $form['field_dismissible_option']['#states'] = [
      'required' => [
        [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    $form['field_banner_title']['widget'][0]['value']['#states'] = [
      'required' => [
        [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    // Unfortunately we can not set ckeditor field as required using
    // states.  So we end up adding this with JS to bypass HTML5 validation
    // and let the validation constraint handle it.
    $form['field_banner_content']['#states'] = [
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];
  }

  /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event):void {
    $this->appendServiceTermDescriptionToVbaFacilityService($event);
    $this->hideBannerFieldsWhenNotEnabled($event);
  }

  /**
   * Hides the VBA facility banner fields when the banner is not enabled.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function hideBannerFieldsWhenNotEnabled(EntityViewAlterEvent $event):void {
    $display = $event->getDisplay();
    if (($display->getTargetBundle() === 'vba_facility')) {
      $vbaNode = $event->getEntity();
      $build = &$event->getBuild();
      if ($vbaNode->field_vba_banner_panel->value == FALSE) {
        if ($build['field_dismissible_option']) {
          $build['field_dismissible_option'] = [];
        }
        if ($build['field_alert_type']) {
          $build['field_alert_type'] = [];
        }
      }
    }
  }

  /**
   * Appends VBA facility service description to title on service node:view.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function appendServiceTermDescriptionToVbaFacilityService(EntityViewAlterEvent $event):void {
    $display = $event->getDisplay();
    if (($display->getTargetBundle() === 'vba_facility_service') && ($display->getOriginalMode() === 'full')) {
      $build = &$event->getBuild();
      $service_node = $event->getEntity();
      $referenced_terms = $service_node->get('field_service_name_and_descripti')->referencedEntities();
      // Render the national service term description (if available).
      if (!empty($referenced_terms)) {
        $description = "";
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
      $formatted_markup = new FormattableMarkup($description, []);
      $build['field_service_name_and_descripti']['#suffix'] = $formatted_markup;
    }
  }

}
