<?php

namespace Drupal\va_gov_vba_facility\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityInterface;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;

use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;

use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\node\NodeInterface;

use Drupal\va_gov_user\Service\UserPermsService;
use Drupal\Component\EventDispatcher\Event;
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
      // FormHookEvents::FORM_ALTER => 'formAlter',

      'hook_event_dispatcher.form_node_vba_facility_edit_form.alter' => 'alterVbaFacilityNodeForm',
      'hook_event_dispatcher.form_node_vba_facility_form.alter' => 'alterVbaFacilityNodeForm',
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
      if($entity->hasField('field_vba_banner_panel')
      && $entity->field_vba_banner_panel->value === 0) {
        $entity->field_banner_title->value = '';

      }
    }

      // if (in_array($entity->bundle(), $facilitiesWithStatus)
      // && ($entity->hasField('field_operating_status_facility'))
      // && ($entity->hasField('field_operating_status_more_info'))) {
      //   $status = $entity->get('field_operating_status_facility')->value;
      //   $details = $entity->get('field_operating_status_more_info')->value;
      //   if ($status === 'normal' && !empty($details)) {
      //     $entity->set('field_operating_status_more_info', '');
      //   }
      // }
    }


    /**
   * Form alterations for staff profile content type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVbaFacilityNodeForm(FormIdAlterEvent $event): void {
    $this->addStateManagementToBannerFields($event);
  }

  /**
   * Form alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    if (!empty($form['#form_id']) && $form['#form_id'] === 'node_vba_facility_form') {
      $this->addStateManagementToBannerFields($form);
    }
  }

   /**
   * Add states management to bio fields to determine visibility based on bool.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function addStateManagementToBannerFields(FormIdAlterEvent $event) {
    $form = &$event->getForm();
    // TODO: Add a library
    // $form['#attached']['library'][] = 'va_gov_profile/set_body_to_required';
    // TODO: For 8 Nov: Start on changing this method
    $selector = ':input[name="field_vba_banner_panel[value]"]';
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

    $form['field_banner_title']['#states'] = [
      'required' => [
        [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    $form['field_body']['widget'][0]['#states'] = [
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
      // Unfortunately we can not set the requiredness of a ckeditor field using
      // states.  So we end up adding this with JS to bypass HTML5 validation
      // and let the validation constraint handle it.
      // This is to prevent the error:
      // An invalid form control with name='field_body[0][value]' is not
      // focusable.
      // because ckeditor changes the id of the field, so when html5 validation
      // kicks in, it can't find the field to hilight as being required.
      // @see https://www.drupal.org/project/drupal/issues/2722319
      // 'required' => [[$selector => ['checked' => TRUE]],],
    ];

    $form['field_body']['#states'] = [
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    $form['field_complete_biography']['#states'] = [
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
  }

  /**
   * Appends VBA facility service description to title on facility node:view.
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
