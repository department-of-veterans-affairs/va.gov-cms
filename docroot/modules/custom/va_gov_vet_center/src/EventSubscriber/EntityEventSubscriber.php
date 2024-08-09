<?php

namespace Drupal\va_gov_vet_center\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\taxonomy\Entity\Term;
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
   * @param \Drupal\va_gov_vet_center\Service\RequiredServices $required_services
   *   The required services service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string translation service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    TranslationInterface $string_translation,
    UserPermsService $user_perms_service,
    RequiredServices $required_services,
    EntityTypeManager $entity_type_manager,
    RendererInterface $renderer
    ) {
    $this->stringTranslation = $string_translation;
    $this->userPermsService = $user_perms_service;
    $this->requiredServices = $required_services;
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => 'entityInsert',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
      EntityHookEvents::ENTITY_VIEW_ALTER => 'entityViewAlter',
      FormHookEvents::FORM_ALTER => 'formAlter',
      'hook_event_dispatcher.form_node_vet_center_facility_health_servi_edit_form.alter' => 'alterVetCenterServiceNodeForm',
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
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event):void {
    $this->appendHealthServiceTermDescriptionToVetCenter($event);
  }

  /**
   * Appends health service entity description to title on entity view page.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function appendHealthServiceTermDescriptionToVetCenter(EntityViewAlterEvent $event):void {
    $display = $event->getDisplay();
    if (($display->getTargetBundle() === 'vet_center') && ($display->getOriginalMode() === 'full')) {
      $build = &$event->getBuild();
      $services = $build['field_health_services'] ?? [];
      foreach ($services as $key => $service) {
        // If there are services (because their keys are numeric.)
        if (is_numeric($key) && !empty($service['#options']['entity'])) {
          $description = new FormattableMarkup('', []);
          $service_node = $service['#options']['entity'];
          $referenced_terms = $service_node->get('field_service_name_and_descripti')->referencedEntities();
          // Render the national service term description (if available).
          if (!empty($referenced_terms)) {
            $referenced_term = reset($referenced_terms);
            if ($referenced_term) {
              $description = $this->getVetServiceDescription($referenced_term);
            }
          }
          else {
            $description = new FormattableMarkup(
            '<div style="color:#e31c3d; font-weight:bold">Notice: The national service name and description were not found. Contact CMS Support to resolve this issue.</div>',
              []);
          }
          // Append the facility-specific service description (no matter what).
          $description .= $service_node->get('field_body')->value;
          $formatted_markup = new FormattableMarkup($description, []);
          $build['field_health_services'][$key]['#suffix'] = $formatted_markup;
        }
      }
    }
  }

  /**
   * Gets the VA Services taxonomy term Vet Center service description.
   *
   * @param \Drupal\taxonomy\Entity\Term $service_term
   *   VA Services taxonomy term.
   *
   * @return \Drupal\Component\Render\FormattableMarkup
   *   Markup of service description.
   */
  private function getVetServiceDescription(Term $service_term) {
    $description = new FormattableMarkup('', []);
    $view_builder = $this->entityTypeManager->getViewBuilder('taxonomy_term');
    $referenced_term_vet_content = $view_builder->view($service_term, 'vet_center_service');
    $vet_term_description = $referenced_term_vet_content["#taxonomy_term"]->get('field_vet_center_service_descrip')->value;
    if ($vet_term_description
        && $this->longEnough($vet_term_description)) {
      $description = $this->renderer->renderRoot($referenced_term_vet_content);
    }
    if (!$vet_term_description || !($this->longEnough($vet_term_description))) {
      $description = $this->getVamcServiceDescription($service_term);
    }
    return $description;
  }

  /**
   * Gets the VA Services VAMC service description.
   *
   * @param \Drupal\taxonomy\Entity\Term $service_term
   *   VA Services taxonomy term.
   *
   * @return \Drupal\Component\Render\FormattableMarkup
   *   Markup of service description.
   */
  private function getVamcServiceDescription(Term $service_term) {
    $view_builder = $this->entityTypeManager->getViewBuilder('taxonomy_term');
    $referenced_term_vamc_content = $view_builder->view($service_term, 'vamc_facility_service');
    $vamc_term_description = $referenced_term_vamc_content["#taxonomy_term"]->get('description')->value;
    if ($vamc_term_description
        && $this->longEnough($vamc_term_description)) {
      $description = $this->renderer->renderRoot($referenced_term_vamc_content);
    }
    else {
      $description = new FormattableMarkup(
        '<div style="color:#e31c3d; font-weight:bold">Notice: The national service description was not found. Contact CMS Support to resolve this issue.</div>',
          []);
    }
    return $description;
  }

  /**
   * Checks service description for adequate length.
   *
   * @param string $service_description
   *   Service description.
   *
   * @return bool
   *   TRUE if over 15 characters (after removing returns and white space).
   */
  private function longEnough(string $service_description) {
    $long_enough = FALSE;
    $body_tags_removed = trim(strip_tags($service_description));
    $body_tags_and_ws_removed = str_replace("\r\n", "", $body_tags_removed);
    // 15 chars or more means the copy should be legitimate.
    if (strlen($body_tags_and_ws_removed) > 15) {
      $long_enough = TRUE;
    }

    return $long_enough;
  }

  /**
   * Alterations to Vet Center - Facility Service node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterServiceNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $is_admin = $this->userPermsService->hasAdminRole(TRUE);
    if (!$is_admin) {
      $this->disableFacilityServiceChange($form, $form_state);
      $this->disableArchivingRequiredServicesNonAdmins($form, $form_state);
    }
    $this->showServiceAsRequiredOrOptional($form, $form_state);

  }

  /**
   * Disable the Archived moderation state on required services for non-admins.
   *
   * @param array $form
   *   The node form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function disableArchivingRequiredServicesNonAdmins(array &$form, FormStateInterface $form_state) {
    $required_services = $this->requiredServices->getRequiredServices();
    $form_object = $form_state->getFormObject();
    $node = $form_object->getEntity();
    $service_id = $node->field_service_name_and_descripti->target_id;
    foreach ($required_services as $required_service) {
      $required_service_id = $required_service->id();
      if ($service_id == $required_service_id) {
        if ($form['moderation_state']['widget'][0]['state']['#options']['archived']) {
          unset($form['moderation_state']['widget'][0]['state']['#options']['archived']);
        }
      }
    }
  }

  /**
   * Show service as required or optional.
   *
   * @param array $form
   *   The node form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function showServiceAsRequiredOrOptional(array &$form, FormStateInterface $form_state) {
    $required_services = $this->requiredServices->getRequiredServices();
    $form_object = $form_state->getFormObject();
    $node = $form_object->getEntity();
    $service_id = $node->field_service_name_and_descripti->target_id;
    $service_name = $node->field_service_name_and_descripti->entity->getName();
    foreach ($required_services as $required_service) {
      $required_service_id = $required_service->id();
      if ($service_id == $required_service_id) {
        // This is a required service.
        $service_required_or_optional = $this->t('a required');
        $can_or_cannot = $this->t('cannot');
        $service_required_or_optional_capitalized = $this->t('Required');
        break;
      }
      else {
        // This is an optional service.
        $service_required_or_optional = $this->t('an optional');
        $can_or_cannot = $this->t('can');
        $service_required_or_optional_capitalized = $this->t('Optional');
      }
    }

    $required_or_optional_markup = new FormattableMarkup(
      '<div class="field-group-tooltip not-editable centralized field-group-html-element tooltip-layout">
      <p><strong>:service_required_or_optional_capitalized Service</strong>
      <br />:service_name is :required_or_optional service. :service_required_or_optional_capitalized services :can_or_cannot be archived. Learn more in the <a href="https://prod.cms.va.gov/help/vet-centers/how-to-edit-a-vet-center-service" target="_blank">Knowledge Base article about Vet Center Services (opens in a new window).</a></p>
      </div>',
      [
        ':service_required_or_optional_capitalized' => $service_required_or_optional_capitalized,
        ':required_or_optional' => $service_required_or_optional,
        ':service_name' => $service_name,
        ':can_or_cannot' => $can_or_cannot,
      ]
    );
    $form['service_optional_or_required'] = [
      '#type' => 'markup',
      '#markup' => $this->t('@markup', ['@markup' => $required_or_optional_markup]),
      '#weight' => 5,
    ];

  }

  /**
   * Disable service name field for existing Vet Center - Facility Services.
   *
   * @param array $form
   *   The node form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function disableFacilityServiceChange(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\node\NodeInterface $node*/
    $node = $form_object->getEntity();
    if (!$node->isNew()) {
      $form['field_service_name_and_descripti']['#disabled'] = TRUE;
    }
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
    $this->disableNameFieldForNonAdmins($form);
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
        five closest locations in an 120-mile radius.</p>

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
   * Hides geolocation field for non-admins.
   *
   * @param array $form
   *   The node form.
   */
  public function hideGeolocationField(array &$form): void {
    if (!$this->userPermsService->hasAdminRole()) {
      $form['field_geolocation']['#access'] = FALSE;
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
    }

    if ($form_id === 'node_vet_center_locations_list_form' || $form_id === 'node_vet_center_locations_list_edit_form') {
      $this->addFacilitiesListingBlockToForm($form);
    }

    $this->optInCapHours($form, $form_state, $form_id);
    $this->hideGeolocationField($form);

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
   * Alterations specific to Vet center content type forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $this->disableNameFieldForNonAdmins($form);
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
      ->accessCheck(FALSE)
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
