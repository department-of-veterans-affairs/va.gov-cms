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
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event): void {
    $this->appendServiceTermDescriptionToVbaFacilityService($event);
  }

  /**
   * Appends VBA facility service description to title on service node:view.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function appendServiceTermDescriptionToVbaFacilityService(EntityViewAlterEvent $event): void {
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
          []
              );
      }
      $formatted_markup = new FormattableMarkup($description, []);
      $build['field_service_name_and_descripti']['#suffix'] = $formatted_markup;
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
    $this->changeBannerType($event);
    $this->changeDismissibleOption($event);
    $this->createLinksFacilityServices($event);
  }

  /**
   * Adds links for creating and managing facility services.
   *
   * One link prepopulates the section and facility for editorial convenience.
   * One link goes to the content search page, passing in the facility name.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  protected function createLinksFacilityServices(FormIdAlterEvent $event): void {
    $form = &$event->getForm();

    if (!isset($form["#fieldgroups"]["group_facility_services"])) {
      return;
    }

    $form_state = $event->getFormState();
    /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    $entity = $form_object->getEntity();
    $section_tid = $entity->field_administration->target_id;
    $facility_nid = $entity->nid->value;
    $create_service_url = "/node/add/vba_facility_service?field_administration=$section_tid&field_office=$facility_nid";
    $create_service_text = $this->t('Create a new service for this facility (opens in new window)');
    $encoded_facility_name = urlencode($entity->title->value);
    $manage_services_url = "/admin/content?title=$encoded_facility_name&type=vba_facility_service&moderation_state=All&owner=All";
    $manage_services_text = $this->t('Manage existing services for this facility (opens in new window)');

    if (isset($form["#fieldgroups"]["group_facility_services"]->format_settings["description"])) {
      $form["#fieldgroups"]["group_facility_services"]->format_settings["description"] = "
        <p><a href='$create_service_url' target='_blank'>$create_service_text</a></p>
        <p><a href='$manage_services_url' target='_blank'>$manage_services_text</a></p>
        ";
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
    $form['#attached']['library'][] = 'va_gov_vba_facility/set_banner_fields_to_required';
    $selector = ':input[name="field_show_banner[value]"]';

    // Show and require the banner fields when show banner is checked.
    $form['field_banner_types_description']['#states'] = [
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    $form['field_alert_type']['widget']['#states'] = [
      'required' => [
        [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    $form['field_dismissible_option']['#states'] = [
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
   * Changes the select list for Banner Type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  protected function changeBannerType(FormIdAlterEvent $event) {
    // Add the '- Select a value -' option to replace '- None -'.
    $form = &$event->getForm();
    if (isset($form['field_alert_type']['widget']['#options']) && array_key_exists('_none', $form['field_alert_type']['widget']['#options'])) {
      $form['field_alert_type']['widget']['#options']['_none'] = '- Select a value -';
    }
  }

  /**
   * Changes the radio buttons for Dismissible option.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  protected function changeDismissibleOption(FormIdAlterEvent $event) {
    // Remove N/A option, which is the result of not being a "required" field.
    $form = &$event->getForm();
    if (isset($form['field_dismissible_option']['widget']['#options']) && array_key_exists('_none', $form['field_dismissible_option']['widget']['#options'])) {
      unset($form['field_dismissible_option']['widget']['#options']['_none']);
    }
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
   * Clear details when banner is not enabled.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function clearBannerFields(EntityInterface $entity): void {
    /** @var \Drupal\node\NodeInterface $entity */
    if ($entity->bundle() === "vba_facility") {
      if (
        $entity->hasField('field_show_banner')
        && $entity->field_show_banner->value == FALSE
      ) {
        if ($entity->field_alert_type) {
          $entity->field_alert_type->value = NULL;
        }
        if ($entity->field_dismissible_option) {
          $entity->field_dismissible_option->value = NULL;
        }
        if ($entity->field_banner_title->value) {
          $entity->field_banner_title->value = '';
        }
        if ($entity->field_banner_content->value) {
          $entity->field_banner_content->value = '';
        }
      }
    }
  }

}
