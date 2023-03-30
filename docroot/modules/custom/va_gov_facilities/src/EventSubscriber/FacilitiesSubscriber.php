<?php

namespace Drupal\va_gov_facilities\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\field_event_dispatcher\Event\Field\WidgetCompleteFormAlterEvent;
use Drupal\field_event_dispatcher\FieldHookEvents;
use Drupal\node\NodeInterface;
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
   */
  public function __construct(
    AccountInterface $currentUser,
    EntityFieldManager $entity_field_manager,
    EntityTypeManager $entityTypeManager,
    MessengerInterface $messenger,
    TranslationInterface $string_translation,
    UserPermsService $user_perms_service
  ) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entity_field_manager;
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
    $this->userPermsService = $user_perms_service;
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
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
      FieldHookEvents::WIDGET_COMPLETE_FORM_ALTER => 'widgetCompleteFormAlter',
      FormHookEvents::FORM_ALTER => 'formAlter',
    ];
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
    // @phpstan-ignore-next-line Test is unclear what get() we are using
    $referenced_entities = $node->get($related_field)->referencedEntities();
    $output = [];
    if (isset($referenced_entities[0])) {
      $referenced_entity = $referenced_entities[0];
      $value = $referenced_entity->get($field_to_render);
      $viewBuilder = \Drupal::entityTypeManager()->getViewBuilder($referenced_entity->getEntityTypeId());
      $output = $viewBuilder->viewField($value, 'full');
      $output['content']['#weight'] = 10;
      $output['#cache']['tags'] = $referenced_entity->getCacheTags();
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
    $this->clearNormalStatusDetails($entity);
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
        $nids = $query->execute();

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
      $fields = [
        'type' => $bundle === 'vet_center' ? 'field_vet_center_type_of_care' : 'field_service_type_of_care',
        'name' => $bundle === 'vet_center' ? 'field_vet_center_friendly_name' : 'field_also_known_as',
        'conditions' => $bundle === 'vet_center' ? 'field_vet_center_com_conditions' : 'field_commonly_treated_condition',
        'description' => $bundle === 'vet_center' ? 'field_vet_center_service_descrip' : 'description',
      ];
    }
    return $fields;
  }

}
