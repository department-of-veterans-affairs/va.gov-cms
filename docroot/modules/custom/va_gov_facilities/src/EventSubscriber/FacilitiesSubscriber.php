<?php

namespace Drupal\va_gov_facilities\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\field_event_dispatcher\Event\Field\WidgetCompleteFormAlterEvent;
use Drupal\field_event_dispatcher\FieldHookEvents;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\va_gov_user\Service\UserPermsService;
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
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   *  The entity field manager.
   */
  private $entityFieldManager;

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
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   *  The renderer.
   */
  private $renderer;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The string entity type service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    AccountInterface $currentUser,
    EntityFieldManager $entity_field_manager,
    EntityTypeManager $entityTypeManager,
    MessengerInterface $messenger,
    TranslationInterface $string_translation,
    UserPermsService $user_perms_service,
    RendererInterface $renderer
  ) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entity_field_manager;
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
    $this->userPermsService = $user_perms_service;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_node_regional_health_care_service_des_edit_form.alter' => 'alterRegionalHealthServiceNodeForm',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_form.alter' => 'alterRegionalHealthServiceNodeForm',
      'hook_event_dispatcher.form_node_vet_center_edit_form.alter' => 'alterVetCenterNodeForm',
      'hook_event_dispatcher.form_node_vet_center_form.alter' => 'alterVetCenterNodeForm',
      'hook_event_dispatcher.form_node_vba_facility_service_edit_form.alter' => 'alterVbaFacilityServiceNodeForm',
      'hook_event_dispatcher.form_node_vba_facility_service_form.alter' => 'alterVbaFacilityServiceNodeForm',
      'hook_event_dispatcher.form_node_vet_center_facility_health_servi_edit_form.alter' => 'alterVetCenterServiceNodeForm',
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
      EntityHookEvents::ENTITY_VIEW_ALTER => 'entityViewAlter',
      FieldHookEvents::WIDGET_COMPLETE_FORM_ALTER => 'widgetCompleteFormAlter',
      FormHookEvents::FORM_ALTER => 'formAlter',
    ];
  }

  /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event):void {
    $this->appendServiceDescriptionToVetCenterFacilityService($event);
  }

  /**
   * Appends Vet Center facility service description to its title on node:view.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function appendServiceDescriptionToVetCenterFacilityService(EntityViewAlterEvent $event):void {
    if (($event->getDisplay()->getTargetBundle() !== 'vet_center_facility_health_servi')
    || ($event->getDisplay()->getOriginalMode() !== 'full')) {
      return;
    }
    $build = &$event->getBuild();
    $service_node = $event->getEntity();
    $referenced_terms = $service_node->get('field_service_name_and_descripti')->referencedEntities();
    // Render the national service term description (if available).
    if (!empty($referenced_terms)) {
      $description = "";
      $referenced_term = reset($referenced_terms);
      if ($referenced_term) {
        $view_builder = $this->entityTypeManager->getViewBuilder('taxonomy_term');
        $referenced_term_content = $view_builder->view($referenced_term, 'vet_center_service');
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

  /**
   * Creates custom render array from field on referenced entity.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node that relates to the one whose field we want to render.
   * @param string $related_field
   *   Field that relates our node to another.
   * @param string $field_to_render
   *   Field we want to render.
   *
   * @return array
   *   Render array
   */
  public static function createRenderArrayFromFieldOnRefdEntity(NodeInterface $node, $related_field, $field_to_render) {
    $output = [];
    // @phpstan-ignore-next-line Test is unclear what get() we are using
    if ($node->hasField($related_field)) {
      // It has the related field. Proceed with trying to grab the reference.
      $referenced_entities = $node->get($related_field)->referencedEntities();
      if (isset($referenced_entities[0])) {
        $referenced_entity = $referenced_entities[0];
        if ($referenced_entity->hasField($field_to_render)) {
          // It has the field we want to render, so proceed.
          $value = $referenced_entity->get($field_to_render);
          $viewBuilder = \Drupal::entityTypeManager()->getViewBuilder($referenced_entity->getEntityTypeId());
          $output = $viewBuilder->viewField($value, 'full');
          $output['content']['#weight'] = 10;
          $output['#cache']['tags'] = $referenced_entity->getCacheTags();
        }
      }
    }
    return $output;
  }

  /**
   * Entity presave Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    $this->clearCustomAppointmentIntroText($entity);
    $this->clearNormalStatusDetails($entity);
    $this->clearUnusedServiceLocationHours($entity);
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
   * Form alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $this->lockTitleEditing($form, $form_state);
    $this->lockApiIdEditing($form, $form_state);
    $this->removeDeleteButton($form);
  }

  /**
   * Remove delete button from edit forms.
   *
   * @param array $form
   *   The form.
   */
  public function removeDeleteButton(array &$form) {
    // Users with any role, other than administrator role,
    // shouldn't be able to delete anything.
    if (!$this->userPermsService->hasAdminRole(TRUE) && isset($form['actions']['delete'])) {
      // Remove the delete button.
      $form['actions']['delete']['#access'] = FALSE;
    }
  }

  /**
   * Locks down API Id for non-admins.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function lockApiIdEditing(array &$form, FormStateInterface $form_state) {
    $form_object = $form_state->getFormObject();
    $bundle = NULL;
    if ($form_object instanceof ContentEntityForm) {
      $bundle = $form_object->getEntity()->bundle();
    }
    if (!$this->userPermsService->hasAdminRole(TRUE) && $bundle === 'health_care_service_taxonomy') {
      $form['field_health_service_api_id']['#disabled'] = TRUE;
    }
    if (!$this->userPermsService->hasAdminRole(TRUE) && $bundle === 'facility_supplemental_status') {
      $form['field_status_id']['#disabled'] = TRUE;
    }
  }

  /**
   * Locks down standardized form titles for non-admins.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function lockTitleEditing(array &$form, FormStateInterface $form_state) {
    $form_object = $form_state->getFormObject();
    $bundle = NULL;
    if ($form_object instanceof ContentEntityForm) {
      $bundle = $form_object->getEntity()->bundle();
    }
    $bundles_with_standardized_titles = [
      'event_listing',
      'health_services_listing',
      'leadership_listing',
      'locations_listing',
      'office',
      'press_releases_listing',
      'publication_listing',
      'story_listing',
    ];
    if (!$this->userPermsService->hasAdminRole() && in_array($bundle, $bundles_with_standardized_titles)) {
      $form['title']['#disabled'] = TRUE;
    }
  }

  /**
   * Widget complete form Event call.
   *
   * @param \Drupal\field_event_dispatcher\Event\Field\WidgetCompleteFormAlterEvent $event
   *   The event.
   */
  public function widgetCompleteFormAlter(WidgetCompleteFormAlterEvent $event): void {
    $widgetCompleteForm = &$event->getWidgetCompleteForm();
    $formState = $event->getFormState();
    $context = $event->getContext();

    $this->getFacilityHours($widgetCompleteForm, $formState, $context);
  }

  /**
   * Gets the facility hours.
   *
   * @param array &$widget_complete_form
   *   The field widget form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $context
   *   The context.
   */
  private function getFacilityHours(array &$widget_complete_form, FormStateInterface $form_state, array $context) {
    /** @var \Drupal\field\Entity\FieldConfig $field_definition */
    $field_definition = $context['items']->getFieldDefinition();
    $paragraph_entity_reference_field_name = $field_definition->getName();
    if ($paragraph_entity_reference_field_name === "field_service_location") {
      $item_list = $context['items'];
      $node = $item_list->getEntity();
      if ($node instanceof NodeInterface) {
        $related_field = "field_facility_location";
        $field_to_render = "field_office_hours";
        $widget_items = $widget_complete_form['widget'];
        for ($i = 0; isset($widget_items[$i]); $i++) {
          $widget_complete_form['widget'][$i]['subform']['field_hours']['facility_hours'] = $this->createRenderArrayFromFieldOnRefdEntity($node, $related_field, $field_to_render);
        }
      }
    }
  }

  /**
   * Clear custom appointment intro text when unused.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function clearCustomAppointmentIntroText(EntityInterface $entity): void {
    if ($entity instanceof ParagraphInterface) {
      $type = $entity->getType();
      /** @var \Drupal\paragraphs\ParagraphInterface $entity */
      if (($type === 'service_location')
      && ($entity->hasField('field_appt_intro_text_type'))
      && ($entity->hasField('field_appt_intro_text_custom'))) {
        $appt_select = $entity->get('field_appt_intro_text_type')->value;
        $appt_leadin = $entity->get('field_appt_intro_text_custom')->value;
        if ($appt_select !== 'customize_text' && !empty($appt_leadin)) {
          $entity->set('field_appt_intro_text_custom', '');
        }
      }
    }
  }

  /**
   * Clear service location hours when unused.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function clearUnusedServiceLocationHours(EntityInterface $entity): void {
    if ($entity instanceof ParagraphInterface) {
      $type = $entity->getType();
      /** @var \Drupal\paragraphs\ParagraphInterface $entity */
      if (($type === 'service_location')
      && ($entity->hasField('field_hours'))
      && ($entity->hasField('field_office_hours'))) {
        $hours = $entity->get('field_hours')->value;
        $office_hours = $entity->get('field_office_hours')->getValue();
        // 2 = Provide specific hours for this service.
        if ($hours !== '2' && count($office_hours) > 0) {
          $entity->set('field_office_hours', []);
        }
      }
    }
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
        $nids = $query->accessCheck(FALSE)->execute();

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

  /**
   * Alterations to Vet center node forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterNodeForm(FormIdAlterEvent $event): void {
    $this->buildHealthServicesDescriptionArrayAddToSettings($event);
  }

  /**
   * Alterations to VAMC system health service node forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterRegionalHealthServiceNodeForm(FormIdAlterEvent $event): void {
    $this->buildRegionalHealthServiceFormIntro($event);
    $this->buildHealthServicesDescriptionArrayAddToSettings($event);
  }

  /**
   * Alterations to VBA Facility service node forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVbaFacilityServiceNodeForm(FormIdAlterEvent $event): void {
    $this->buildHealthServicesDescriptionArrayAddToSettings($event);
  }

  /**
   * Alterations to the Vet Center facility service node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterServiceNodeForm(FormIdAlterEvent $event): void {
    $this->buildHealthServicesDescriptionArrayAddToSettings($event);
  }

  /**
   * Builds an array of descriptions from health services available on form.
   *
   * Adds the descriptions array built by this method to drupalSettings.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function buildHealthServicesDescriptionArrayAddToSettings(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $entity_type = 'taxonomy_term';
    $bundle = 'health_care_service_taxonomy';
    $fields = $this->getProductTypeTermFields($form, $form_state);
    $service_terms = $this->entityTypeManager
      ->getListBuilder($entity_type)
      ->getStorage()
      ->loadByProperties([
        'vid' => $bundle,
      ]);
    // Use this to grab values in the term parent vocab.
    $vocabulary_definition = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    $descriptions = [];
    foreach ($service_terms as $service_term) {
      $description = $service_term->get($fields['description'])->value ?? '';
      // Lovell VAMC Facilities may include a TRICARE description field.
      $tricare_description = $service_term->get('field_tricare_description')->value ?? '';
      /** @var \Drupal\taxonomy\Entity\Term $service_term */
      $descriptions[$service_term->id()] = [
        'type' => $service_term->get($fields['type'])->getSetting('allowed_values')[$service_term->get($fields['type'])->getString()] ?? NULL,
        'name' => $service_term->get($fields['name'])->getString(),
        'conditions' => $service_term->get($fields['conditions'])->getString(),
        'description' => trim(strip_tags($description)),
        'tricare_description' => trim(strip_tags($tricare_description)),
        'vc_vocabulary_service_description_label' => $vocabulary_definition['field_vet_center_service_descrip']->getLabel(),
        'vc_vocabulary_description_help_text' => $vocabulary_definition['field_vet_center_service_descrip']->getDescription(),
        // VBA has nationalized content we want to show from the vocabulary.
        'vba_regional_service_header' => $service_term->hasField('field_regional_service_header')
          ? trim($service_term->get('field_regional_service_header')->getString()) : '',
        'vba_regional_service_description' => $service_term->hasField('field_regional_service_descripti')
          ? trim($service_term->get('field_regional_service_descripti')->getString()) : '',
        'vba_facility_service_header' => $service_term->hasField('field_facility_service_header')
          ? trim($service_term->get('field_facility_service_header')->getString()) : '',
        'vba_facility_service_description' => $service_term->hasField('field_facility_service_descripti')
          ? trim($service_term->get('field_facility_service_descripti')->getString()) : '',
      ];
    }
    $form['#attached']['drupalSettings']['availableHealthServices'] = $descriptions;
    $form['#attached']['library'][] = 'va_gov_facilities/display_service_descriptions';
  }

  /**
   * Builds h2 of VAMC System Health Service page type name and adds help text.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function buildRegionalHealthServiceFormIntro(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $formatted_markup = new FormattableMarkup('<div class="services-intro-wrap"><h2>VAMC System Health Service</h2>
    <p>Add services that Veterans can receive at one or more facilities in your health system.
    Some content won’t be editable because it comes from other sources. For full guidance,
    see <a target="_blank" href="@help_link">How to edit a VAMC System Health Service (opens in a new tab)</a>.</p></div>', [
      '@help_link' => 'https://prod.cms.va.gov/help/vamc/how-to-add-a-vamc-system-health-service',
    ]);
    $form['field_service_name_and_descripti']['#prefix'] = $this->t('@markup', ['@markup' => $formatted_markup]);
  }

  /**
   * Builds an array of term fields predicated by product type.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function getProductTypeTermFields(array &$form, FormStateInterface $form_state) {
    $fields = [];
    if ($form_state->getFormObject() instanceof EntityFormInterface) {
      $bundle = $form_state->getFormObject()->getEntity()->bundle();
      // Make the bundle available to displayServiceDescriptions.js.
      $form['#attached']['drupalSettings']['currentNodeBundle'] = $bundle;
      $fields = $this->getVaServicesTaxonomyFieldNames($bundle);
    }
    return $fields;
  }

  /**
   * Gets the VA Services field names for each type of product.
   *
   * @param string $node_type
   *   The type of node.
   *
   * @return array
   *   The field names of the service of the node type.
   */
  public function getVaServicesTaxonomyFieldNames(string $node_type) : array {
    $vaServicesFields = [];
    switch ($node_type) {
      case 'regional_health_care_service_des':
        $vaServicesFields = [
          'type' => 'field_service_type_of_care',
          'name' => 'field_also_known_as',
          'conditions' => 'field_commonly_treated_condition',
          'description' => 'description',
        ];
        break;

      case 'vet_center_facility_health_servi':
      case 'vet_center':
        $vaServicesFields = [
          'type' => 'field_vet_center_type_of_care',
          'name' => 'field_vet_center_friendly_name',
          'conditions' => 'field_vet_center_com_conditions',
          'description' => 'field_vet_center_service_descrip',
        ];
        break;

      case 'vba_facility_service':
        $vaServicesFields = [
          'type' => 'field_vba_type_of_care',
          'name' => 'field_vba_friendly_name',
          'conditions' => 'field_vba_com_conditions',
          'description' => 'field_vba_service_descrip',
        ];
        break;
    }
    return $vaServicesFields;
  }

}
