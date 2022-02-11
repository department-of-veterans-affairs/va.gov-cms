<?php

namespace Drupal\va_gov_vet_center\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Drupal\va_gov_vet_center\Service\RequiredServices;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
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
   * Vet center required service service.
   *
   * @var \Drupal\va_gov_vet_center\Service\RequiredServices
   *  The required services service.
   */
  protected $requiredServices;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * Constructs a EntityEventSubscriber object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The string translation service.
   * @param \Drupal\va_gov_vet_center\Service\RequiredServices $required_services
   *   The required services service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string translation service.
   */
  public function __construct(
    TranslationInterface $string_translation,
    UserPermsService $user_perms_service,
    RequiredServices $required_services,
    EntityTypeManager $entity_type_manager
    ) {
    $this->stringTranslation = $string_translation;
    $this->userPermsService = $user_perms_service;
    $this->requiredServices = $required_services;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'entityInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'entityUpdate',
      HookEventDispatcherInterface::FORM_ALTER => 'formAlter',
      'hook_event_dispatcher.form_node_vet_center_locations_list_form.alter' => 'alterVetCenterLocationsListNodeForm',
      'hook_event_dispatcher.form_node_vet_center_locations_list_edit_form.alter' => 'alterVetCenterLocationsListNodeForm',
      'hook_event_dispatcher.form_node_vet_center_mobile_vet_center_form.alter' => 'alterVetCenterMvcNodeForm',
      'hook_event_dispatcher.form_node_vet_center_mobile_vet_center_edit_form.alter' => 'alterVetCenterMvcNodeForm',
      'hook_event_dispatcher.form_node_vet_center_outstation_form.alter' => 'alterVetCenterOutstationNodeForm',
      'hook_event_dispatcher.form_node_vet_center_outstation_edit_form.alter' => 'alterVetCenterOutstationNodeForm',
      'hook_event_dispatcher.form_node_vet_center_form.alter' => 'alterVetCenterNodeForm',
      'hook_event_dispatcher.form_node_vet_center_edit_form.alter' => 'alterVetCenterNodeForm',

    ];
  }

  /**
   * Alterations to Vet center MVC node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterMvcNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_vet_center/limit_vet_service_selections';
  }

  /**
   * Alterations to Vet center outstation node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterOutstationNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_vet_center/limit_vet_service_selections';
  }

  /**
   * Alterations to Vet center nearby locations node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterLocationsListNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_vet_center/limit_vet_service_selections';
    $this->buildNearbyVCandOutStationFieldsetContent($event);
  }

  /**
   * Build the VC & Outstation fieldset and populate with help text.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function buildNearbyVcAndOutStationFieldsetContent(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form_object = $event->getFormState()->getFormObject();
    if ($form_object instanceof EntityFormInterface) {
      $nid = $form_object->getEntity()->id() ?? NULL;
      $formatted_markup = new FormattableMarkup(
      '<p class="vc-help-text"><strong>Review nearby locations</strong>
      <br />Nearby locations provide alternative
        options to the main and satellite locations and are automatically selected based on the
        five closest locations in an 80-mile radius.</p>

        <p class="vc-help-text"><a target="_blank" href="@preview_link">Preview page to review nearby locations (opens in a new tab)</a></p>

        <p class="vc-help-text"><strong>Doesn\'t look right?</strong>
        <br />If you believe the selected nearby locations aren\'t appropriate to Veterans
        in your area, <a target="_blank" href="@help_link">contact the CMS Helpdesk (opens in a new tab)</a></p>',
      [
        '@preview_link' => 'http://preview-prod.vfs.va.gov/preview?nodeId=' . $nid . '#other-near-locations',
        '@help_link' => 'https://va-gov.atlassian.net/servicedesk/customer/portal/3/group/8/create/26',
      ]
      );

      $form['nearby_vc_information'] = [
        '#type' => 'details',
        '#weight' => 5,
        '#title' => $this->t('Nearby Vet Centers and Outstations'),
        '#description' => $this->t('@markup', ['@markup' => $formatted_markup]),
        '#open' => TRUE,
      ];
    }
  }

  /**
   * Entity insert.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() == 'node') {
      $this->requiredServices->addRequiredServicesByFacility($entity);
    }
    if ($entity->getEntityTypeId() === 'taxonomy_term') {
      $this->requiredServices->addRequiredServicesByTerm($entity);
    }
  }

  /**
   * Entity update.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'taxonomy_term') {
      $this->requiredServices->addRequiredServicesByTerm($entity);
    }
  }

  /**
   * Disabled official name field on vc forms when user is non admin.
   *
   * @param array $form
   *   The node form.
   */
  public function disableNameFieldForNonAdmins(array &$form): void {
    if (!$this->userPermsService->hasAdminRole()) {
      $form['field_official_name']['#disabled'] = TRUE;
    }
  }

  /**
   * Alterations to Vet center forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $form_id = $event->getFormId();

    if ($form_id === 'node_vet_center_cap_form' || $form_id === 'node_vet_center_cap_edit_form') {
      // Add after_build callbacks for VC CAP node forms.
      $form['field_address']['widget']['#after_build'][] = 'va_gov_vet_center_vc_cap_address_alter_label_after_build';
      $form['field_facility_hours']['widget']['#after_build'][] = 'va_gov_vet_center_vc_cap_hours_hide_caption_after_build';
    }

    if ($form_id === 'node_vet_center_locations_list_form' || $form_id === 'node_vet_center_locations_list_edit_form') {
      $this->addFacilitiesListingBlockToForm($form);
    }

    $this->optInCapHours($form, $form_state, $form_id);

    // List of forms to modify media library widget help text.
    $media_widget_content_form_ids = [
      'node_vet_center_cap_form',
      'node_vet_center_cap_edit_form',
      'node_vet_center_edit_form',
      'node_vet_center_form',
      'node_vet_center_mobile_vet_center_form',
      'node_vet_center_mobile_vet_center_edit_form',
      'node_vet_center_outstation_form',
      'node_vet_center_outstation_edit_form',
    ];

    // We want to modify media library widget help text.
    if (in_array($form_id, $media_widget_content_form_ids)) {
      $form['field_media']['widget']['#field_prefix']['empty_selection'] = [
        '#markup' => $this->t('Add a photo of the facility'),
      ];
    }
    // Require message on revision.
    $this->requireRevisionMessage($form, $form_state, $form_id);
  }

  /**
   * Output facility listing view on vc locations node forms.
   *
   * @param array $form
   *   The form array.
   */
  public function addFacilitiesListingBlockToForm(array &$form) {
    $form['group_my_locations'] = [
      '#type' => 'details',
      '#title' => $this->t('Main and satellite locations'),
      '#open' => TRUE,
      '#weight' => 3,
    ];
    $form['group_my_locations']['vc_facility_listing_view'] = [
      '#type' => 'view',
      '#name' => 'vet_center_facility_listing',
      '#display_id' => 'vc_listing_for_node_form',
      '#embed' => TRUE,
    ];
  }

  /**
   * Determine whether or not user can edit community access point office hours.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string $form_id
   *   The form id.
   */
  public function optInCapHours(array &$form, FormStateInterface $form_state, $form_id) {
    if (($form_id === 'node_vet_center_cap_edit_form') || ($form_id === 'node_vet_center_cap_form')) {
      /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
      $form_object = $form_state->getFormObject();
      /** @var \Drupal\node\NodeInterface $node*/
      $node = $form_object->getEntity();
      // We want to implement the states logic only if the field_office_hours
      // and the field_vetcenter_cap_hours_opt_in are available.
      if (($node->hasField('field_office_hours')) && ($node->hasField('field_vetcenter_cap_hours_opt_in'))) {
        $form['field_office_hours']['#states'] = [
          'visible' => [
            ':input[name="field_vetcenter_cap_hours_opt_in"]' => ['value' => '1'],
          ],
        ];
      }
    }
  }

  /**
   * Adds Validation to check revision log message is added.
   *
   * @param array $form
   *   The exposed widget form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string $form_id
   *   The form id.
   */
  public function requireRevisionMessage(array &$form, FormStateInterface &$form_state, $form_id) {
    $vc_types = [
      'node_vet_center_edit_form',
      'node_vet_center_cap_edit_form',
      'node_vet_center_facility_health_servi_edit_form',
      'node_vet_center_locations_list_edit_form',
      'node_vet_center_mobile_vet_center_edit_form',
      'node_vet_center_outstation_edit_form',
    ];
    // Vet centers need to have revision log messages on edit.
    if (in_array($form_id, $vc_types)) {
      $widget_fields = [
        'field_nearby_vet_centers',
        'field_nearby_mobile_vet_centers',
      ];
      foreach ($widget_fields as $widget_field) {
        // Stop the node form validation to fire on the removal buttons.
        $current_widgets = $form[$widget_field]['widget']['current'] ?? [];
        foreach ($current_widgets as $key => $button) {
          if (is_numeric($key)) {
            $form[$widget_field]['widget']['current'][$key]['actions']['remove_button']['#limit_validation_errors'] = [['field_nearby_vet_centers']];
          }
        }
      }
      $form['revision_log']['#required'] = TRUE;
      $form['revision_log']['widget']['#required'] = TRUE;
      $form['revision_log']['widget'][0]['#required'] = TRUE;
      $form['revision_log']['widget'][0]['value']['#required'] = TRUE;
      $form['#validate'][] = '_va_gov_backend_validate_required_revision_message';
    }
  }

  /**
   * Alterations specific to Vet center content type forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $form['#attached']['library'][] = 'va_gov_vet_center/set_ief_service_selects';
    $this->modifyIefServicesFormDisplay($form, $form_state);
    $this->disableNameFieldForNonAdmins($form);
  }

  /**
   * Add column to services table.
   *
   * Sort alphanumerically.
   *
   * Remove delete options for required items.
   *
   * Remove drag and drop.
   *
   * @param array $form
   *   The node form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function modifyIefServicesFormDisplay(array &$form, FormStateInterface $form_state) {
    $form_object = $form_state->getFormObject();
    if ($form_object instanceof EntityFormInterface) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $form_object->getEntity();
      $node_title = $node->getTitle();
      $form['field_health_services']['widget']['entities']['#table_fields']['label']['label'] = $this->t('Services offered at :title', [':title' => $node_title]);
      $cols = &$form['field_health_services']['widget']['entities']['#table_fields'];
      $cols['req_optional'] = [
        'type' => 'markup',
        'label' => $this->t('Required/optional'),
        'weight' => 3,
      ];
      unset($form['field_health_services']['widget']['entities']['#table_fields'][0]);
      $keys = Element::children($form['field_health_services']['widget']['entities']);
      if (!empty($keys)) {
        foreach ($keys as $key) {
          $entity = &$form['field_health_services']['widget']['entities'][$key];
          $entity['#markup'] = $this->t('Optional');
          if ($this->checkIfServiceRequired($entity['#label'])) {
            unset($entity['actions']['ief_entity_remove']);
            $entity['#markup'] = $this->t('Required');
          }
        }
      }
    }
  }

  /**
   * Checks if service is required.
   *
   * @param string $service
   *   The service name.
   *
   * @return bool
   *   True is service is required.
   */
  public function checkIfServiceRequired($service) {
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $term_tids = $term_storage->getQuery()
      ->condition('vid', 'health_care_service_taxonomy')
      ->condition('field_vet_center_required_servic', 1, '=')
      ->execute();
    $terms = $term_storage->loadMultiple($term_tids);
    $required_services = [];
    foreach ($terms as $term) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $required_services[] = $term->getName();
    }
    $service_cleaned = explode(' - ', $service)[1];
    return in_array($service_cleaned, $required_services);
  }

}
