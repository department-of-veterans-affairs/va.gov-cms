<?php

namespace Drupal\va_gov_vet_center\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
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
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string translation service.
   */
  public function __construct(TranslationInterface $string_translation, EntityTypeManager $entity_type_manager) {
    $this->stringTranslation = $string_translation;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event):void {
    $this->appendHealthServiceTermDescription($event);
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
   * Builds an array of descriptions from health services available on form.
   *
   * Adds the descriptions array built by this method to drupalSettings.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function buildHealthServicesDescriptionArrayAddToSettings(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $service_terms = $this->entityTypeManager
      ->getListBuilder('taxonomy_term')
      ->getStorage()
      ->loadByProperties([
        'vid' => 'health_care_service_taxonomy',
      ]);
    $descriptions = [];
    foreach ($service_terms as $service_term) {
      /** @var \Drupal\taxonomy\Entity\Term $service_term */
      $descriptions[$service_term->id()] = [
        'type' => $service_term->get('field_vet_center_type_of_care')->getSetting('allowed_values')[$service_term->get('field_vet_center_type_of_care')->getString()] ?? NULL,
        'name' => $service_term->get('field_vet_center_friendly_name')->getString(),
        'conditions' => $service_term->get('field_vet_center_com_conditions')->getString(),
        'description' => trim(strip_tags($service_term->get('field_vet_center_service_descrip')->value)),
      ];
    }
    $form['#attached']['drupalSettings']['availableHealthServices'] = $descriptions;
    $form['#attached']['library'][] = 'va_gov_vet_center/display_service_descriptions';
  }

  /**
   * Appends health service entity description to title on entity view page.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function appendHealthServiceTermDescription(EntityViewAlterEvent $event):void {
    if ($event->getDisplay()->getTargetBundle() === 'vet_center') {
      $build = &$event->getBuild();
      $services = $build['field_health_services'];
      foreach ($services as $key => $service) {
        if (is_numeric($key)) {
          $service_node = $service['#options']['entity'];
          // Magic method is more reliable in this instance.
          // When doing $service_node->get('field_body')->first()->get('value')->getString(),
          // Null errors can be thrown if body is empty when using first().
          // Also check that content isn't just tags and linebreaks.
          // Allow a tolerance of 15 characters.
          $description = strlen(str_replace("\r\n", "", trim(strip_tags($service_node->get('field_body')->value)))) > 15
          ? '<br />' . trim($service_node->get('field_body')->value)
          // Magic method necessary here.
          // Trying to set var Drupal\taxonomy\Entity\Terminterface
          // In this context isn't working as expected, making
          // $service_node->get('field_service_name_and_descripti')->getEntity()->getDescription()->getString()
          // fail.
          : '<br />' . trim($service_node->get('field_service_name_and_descripti')->entity->get('field_vet_center_service_descrip')->value);
          $formatted_markup = new FormattableMarkup($description, []);
          $build['field_health_services'][$key]['#suffix'] = $formatted_markup;
        }
      }

    }
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
        '@preview_link' => $_SERVER['REQUEST_SCHEME'] . '://preview-' . $_SERVER['HTTP_HOST'] . '/preview?nodeId=' . $nid,
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
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on Vet center node forms.
      'hook_event_dispatcher.form_node_vet_center_form.alter' => 'alterVetCenterNodeForm',
      'hook_event_dispatcher.form_node_vet_center_edit_form.alter' => 'alterVetCenterNodeForm',
      // React on Vet center locations list edit form.
      'hook_event_dispatcher.form_node_vet_center_locations_list_edit_form.alter' => 'alterVetCenterLocationsListNodeEditForm',
      // React on Vet center node view.
      HookEventDispatcherInterface::ENTITY_VIEW_ALTER => 'entityViewAlter',
    ];
  }

}
