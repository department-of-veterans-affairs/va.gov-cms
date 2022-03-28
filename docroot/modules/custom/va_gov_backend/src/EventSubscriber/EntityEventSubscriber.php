<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\node\NodeInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementFormAlterEvent;
use Drupal\field_event_dispatcher\FieldHookEvents;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The User Perms Service.
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
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The current user perms service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string entity type service.
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field service.
   */
  public function __construct(
    TranslationInterface $string_translation,
    UserPermsService $user_perms_service,
    EntityTypeManager $entity_type_manager,
    EntityFieldManager $entity_field_manager
  ) {
    $this->stringTranslation = $string_translation;
    $this->userPermsService = $user_perms_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
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
      $this->trimNodeTitleWhitespace($entity);
    }
  }

  /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event):void {
    $this->appendHealthServiceTermDescriptionToVetCenter($event);
    $this->showUnspecifiedWhenSystemEhrNumberEmpty($event);
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
   * Alterations to VAMC regional health service node forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterRegionalHealthServiceNodeForm(FormIdAlterEvent $event): void {
    $this->buildRegionalHealthServiceFormIntro($event);
    $this->buildHealthServicesDescriptionArrayAddToSettings($event);
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
      /** @var \Drupal\taxonomy\Entity\Term $service_term */
      $descriptions[$service_term->id()] = [
        'type' => $service_term->get($fields['type'])->getSetting('allowed_values')[$service_term->get($fields['type'])->getString()] ?? NULL,
        'name' => $service_term->get($fields['name'])->getString(),
        'conditions' => $service_term->get($fields['conditions'])->getString(),
        'description' => trim(strip_tags($service_term->get($fields['description'])->value)),
        'vc_vocabulary_service_description_label' => $vocabulary_definition['field_vet_center_service_descrip']->getLabel(),
        'vc_vocabulary_description_help_text' => $vocabulary_definition['field_vet_center_service_descrip']->getDescription(),
      ];
    }
    $form['#attached']['drupalSettings']['availableHealthServices'] = $descriptions;
    $form['#attached']['library'][] = 'va_gov_backend/display_service_descriptions';
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

  /**
   * Appends health service entity description to title on entity view page.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function appendHealthServiceTermDescriptionToVetCenter(EntityViewAlterEvent $event):void {
    if ($event->getDisplay()->getTargetBundle() === 'vet_center') {
      $build = &$event->getBuild();
      $services = $build['field_health_services'] ?? [];
      foreach ($services as $key => $service) {
        if (is_numeric($key) && !empty($service['#options'])) {
          $service_node = $service['#options']['entity'];

          // Look for real content in field_body. If just line breaks
          // and empty tags use field_service_name_and_descripti.
          $body_tags_removed = trim(strip_tags($service_node->get('field_body')->value));
          $body_tags_and_ws_removed = str_replace("\r\n", "", $body_tags_removed);
          $description = strlen($body_tags_and_ws_removed) > 15
          ? '<br />' . trim($service_node->get('field_body')->value)
          : '<br />' . trim($service_node->get('field_service_name_and_descripti')->entity->get('field_vet_center_service_descrip')->value);

          $formatted_markup = new FormattableMarkup($description, []);
          $build['field_health_services'][$key]['#suffix'] = $formatted_markup;
        }
      }
    }
  }

  /**
   * Shows the text "Unspecified" when phone number is blank.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function showUnspecifiedWhenSystemEhrNumberEmpty(EntityViewAlterEvent $event):void {
    if ($event->getDisplay()->getTargetBundle() === 'health_care_region_page') {
      $build = &$event->getBuild();
      if (empty($build['field_va_health_connect_phone']['#title'])) {
        $undefined_number_text = '
          <div class="field field--name-field-va-health-connect-phone field--type-list-string field--label-above">
              <div class="field__label">VA Health Connect phone number</div>
              <div class="field__item">Undefined</div>
          </div>';

        $formatted_markup = new FormattableMarkup($undefined_number_text, []);
        $build['field_va_health_connect_phone']['#prefix'] = $formatted_markup;
      }
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
  }

  /**
   * Trim any preceding and trailing whitespace on node titles.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to be modified.
   */
  private function trimNodeTitleWhitespace(NodeInterface $node) {
    $title = $node->getTitle();
    // Trim leading and then trailing separately to avoid a convoluted regex.
    $title = preg_replace('/^\s+/', '', $title);
    $title = preg_replace('/\s+$/', '', $title);
    $node->setTitle($title);
  }

  /**
   * Form alterations for staff profile content type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterstaffProfileNodeForm(FormIdAlterEvent $event): void {
    $this->addStateManagementToBioFields($event);
  }

  /**
   * Add states management to bio fields to determine visibility based on bool.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function addStateManagementToBioFields(FormIdAlterEvent $event) {
    $form = &$event->getForm();
    $form['#submit'][] = $this->addStateManagementToBioFieldsSubmitHandler($event);
    $form['#attached']['library'][] = 'va_gov_backend/set_body_to_required';
    $selector = ':input[name="field_complete_biography_create[value]"]';
    $form['field_intro_text']['widget'][0]['value']['#states'] = [
      'required' => [
          [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
            [$selector => ['checked' => TRUE]],
      ],
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
   * Submit handler removes field body req when bio toggle is set to FALSE.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function addStateManagementToBioFieldsSubmitHandler(FormIdAlterEvent $event) {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $bio_display = !empty($form_state->getUserInput()['field_complete_biography_create']['value']) ? TRUE : FALSE;
    if (!$bio_display) {
      $form['field_body']['widget'][0]['#required'] = FALSE;
    }
  }

  /**
   * Form alterations for centralized content edit form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterCentralizedContentNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $this->lockCentralizedContentFields($form);
    $this->lockCcDocumentorParagraphs($form);
  }

  /**
   * Locks down form paragraphs for non-admins.
   *
   * @param array $form
   *   The form.
   */
  public function lockCcDocumentorParagraphs(array &$form) {
    if (!$this->userPermsService->hasAdminRole(TRUE)) {
      // Loop through our descriptor & wysiwyg paragraphs, and add special
      // treatment class and header tags, and disable wysiwygs.
      foreach ($form['field_content_block']['widget'] as $key => $cc_paragraph) {
        if (is_numeric($key) && $cc_paragraph['#paragraph_type'] === 'centralized_content_descriptor') {
          $form['field_content_block']['widget'][$key]['_weight']['#attributes']['disabled'] = TRUE;
          $form['field_content_block']['widget'][$key]['#attributes']['class'] = [
            'cc-special-treatment-paragraph',
            $form['field_content_block']['widget'][$key]['#paragraph_type'],
          ];
          $title = $form['field_content_block']['widget'][$key]['subform']['field_cc_documentor_title']['widget'][0]['value']['#default_value'];
          $title = $this->t(':title', [':title' => $title]);
          $description = $form['field_content_block']['widget'][$key]['subform']['field_cc_documentor_description']['widget'][0]['#default_value'];
          $description = $this->t(':description', [':description' => $description]);
          $form['field_content_block']['widget'][$key]['#prefix'] = "<h3>{$title}</h3><p>{$description}</p>";
        }
      }
    }
  }

  /**
   * Locks down form fields for non-admins.
   *
   * @param array $form
   *   The form.
   */
  public function lockCentralizedContentFields(array &$form) {
    // All users should see this intro.
    $about = $this->t('About');
    $description = $this->t("Centralized content ensures that Veterans receive the same information about certain topics no matter where they're presented on VA.gov. Here you'll mangage content components that contain pieces of standardized information. Review the about info below to understand where this content will be applied.");
    $form['title']['#prefix'] = "<h2>{$about}</h2><p>{$description}</p>";
    $form['title']['#attributes'] = [
      'class' => ['cc-special-treatment-field'],
    ];
    $question = $this->t('Need to add a new centralized content component not listed here?');
    $contact = $this->t('Contact the product owner associated with this content.');
    $form['field_content_block']['#suffix'] = "<div class=\"cc-suffix-text\"><strong>{$question}</strong><br />{$contact}</div>";

    if (!$this->userPermsService->hasAdminRole(TRUE)) {
      $form['#attached']['library'][] = 'va_gov_backend/centralized_content_alterations';

      // For non-admin replace field with static content.
      unset($form['field_content_block']['widget']['#title']);
      $form['field_content_block']['widget']['#title'] = '<h2>' . $this->t('Content') . '</h2>';

      // For non-admin replace body field with static content
      // generated from field value.
      if (!empty($form['body']['widget'][0]['#default_value'])) {
        $form['body'] = [
          '#markup' => "<h3>{$this->t('Purpose')}</h3><div class=\"cc-wysi-wrap\">{$form['body']['widget'][0]['#default_value']}</div>",
        ];
      }
      else {
        unset($form['body']);
      }

      // For non-admin replace product field with static content
      // generated from field value and disable editing.
      if (!empty($form['field_product']['widget']['#default_value'][0])) {
        $title = $this->t(':title', [':title' => $form['field_product']['widget']['#title']]);
        $description = $this->t(':description', [':description' => $form['field_product']['widget']['#options'][$form['field_product']['widget']['#default_value'][0]]]);
        $form['field_product'] = [
          '#prefix' => "<h3>{$title}</h3><p class=\"cc-p\">{$description}</p>",
          '#attributes' => [
            'class' => ['cc-special-treatment-field'],
          ],
        ];
      }
      else {
        unset($form['field_product']);
      }

      // For non-admin replace applied to field with static content
      // generated from field value and disable editing.
      if (!empty($form['field_applied_to']['widget'][0]['value']['#default_value'])) {
        $title = $this->t(':title', [':title' => $form['field_applied_to']['widget'][0]['#title']]);
        $description = $this->t(':description', [':description' => $form['field_applied_to']['widget'][0]['value']['#default_value']]);
        $form['field_applied_to'] = [
          '#prefix' => "<h3>{$title}</h3><p class=\"cc-p\">{$description}</p>",
          '#attributes' => [
            'class' => ['cc-special-treatment-field'],
          ],
        ];
      }
      else {
        unset($form['field_applied_to']);
      }
    }
  }

  /**
   * Widget form alter Event call.
   *
   * @param \Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementFormAlterEvent $event
   *   The event.
   */
  public function formWidgetAlter(WidgetSingleElementFormAlterEvent $event): void {
    $form = &$event->getElement();
    $form_state = $event->getFormState();
    $this->removeCollapseButton($form);
    $this->toggleFieldOfficeHours($form, $form_state);
  }

  /**
   * Remove collapse button on button paragraphs widget forms.
   *
   * @param array $form
   *   The form.
   */
  public function removeCollapseButton(array &$form) {
    if (!empty($form['#paragraph_type']) && $form['#paragraph_type'] === 'button') {
      unset($form['top']['actions']['actions']['collapse_button']);
    }
  }

  /**
   * SHow or hide field_office_hours on service_location paragraph widget forms.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function toggleFieldOfficeHours(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\node\NodeForm $form_object $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $form_object->getEntity();
    // The new use of field_office_hours on service location paragraphs should
    // be visible to admins only except on non-clinical service pages, where
    // it should be the only hours field used.
    if (!empty($form['#paragraph_type'])
    && $form['#paragraph_type'] === 'service_location'
    && !$this->userPermsService->hasAdminRole(TRUE)) {
      if ($entity->bundle() === 'vha_facility_nonclinical_service') {
        // We are on the new version, remove the old version of the field.
        unset($form['subform']['field_facility_service_hours']);
      }
      else {
        // We are not using the new version yet, so remove it.
        unset($form['subform']['field_office_hours']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
      FieldHookEvents::WIDGET_SINGLE_ELEMENT_FORM_ALTER => 'formWidgetAlter',
      EntityHookEvents::ENTITY_VIEW_ALTER => 'entityViewAlter',
      FormHookEvents::FORM_ALTER => 'formAlter',
      'hook_event_dispatcher.form_node_person_profile_form.alter' => 'alterStaffProfileNodeForm',
      'hook_event_dispatcher.form_node_person_profile_edit_form.alter' => 'alterStaffProfileNodeForm',
      'hook_event_dispatcher.form_node_centralized_content_edit_form.alter' => 'alterCentralizedContentNodeForm',
      'hook_event_dispatcher.form_node_vet_center_form.alter' => 'alterVetCenterNodeForm',
      'hook_event_dispatcher.form_node_vet_center_edit_form.alter' => 'alterVetCenterNodeForm',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_form.alter' => 'alterRegionalHealthServiceNodeForm',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_edit_form.alter' => 'alterRegionalHealthServiceNodeForm',
    ];
  }

}
