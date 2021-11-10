<?php

namespace Drupal\va_gov_vet_center\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * Constructs a EntityEventSubscriber object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * Alterations to Vet center nearby locations edit form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVetCenterLocationsListNodeEditForm(FormIdAlterEvent $event): void {
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
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'formAlter',
      // React on Vet center locations list edit form.
      'hook_event_dispatcher.form_node_vet_center_locations_list_edit_form.alter' => 'alterVetCenterLocationsListNodeEditForm',
    ];
  }

}
