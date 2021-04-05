<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\post_api\Service\AddToQueue;

/**
 * Class PostFacilityService posts specific service info to Lighthouse.
 */
class PostFacilityService {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A array of any errors in prepping the data.
   *
   * @var array
   */
  protected $errors = [];

  /**
   * The facility service node.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $facilityService;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Post queue add service.
   *
   * @var \Drupal\post_api\Service\AddToQueue
   */
  protected $postQueue;

  /**
   * The related system service node.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $systemService;

  /**
   * The related health system taxonomy service term.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $serviceTerm;

  /**
   * The services that should be pushed to Lighthouse.
   *
   * For now we are only pushing covid 19 services. The key is only for
   * making sense of code, the TID is what is used for comparison.
   *
   * @var array
   */
  protected $servicesToPush = [
    // Key: service name (not used) => Value: TID.
    'COVID-19 vaccines' => 321,
  ];

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Constructs a new PostFacilityService object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\post_api\Service\AddToQueue $post_queue
   *   The PostAPI service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger, MessengerInterface $messenger, AddToQueue $post_queue) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->messenger = $messenger;
    $this->postQueue = $post_queue;
  }

  /**
   * Adds facility service data to Post API queue.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @return int
   *   The count of the number of items queued (1,0).
   */
  public function queueFacilityService(EntityInterface $entity) {
    $this->errors = [];
    if (($entity->getEntityTypeId() === 'node') && ($entity->bundle() === 'health_care_local_health_service')) {
      // This is an appropriate service so begin gathering data to process.
      $this->facilityService = $entity;
      // Many service details do not reside with the facility service node.
      // They must be derived from the facility and system service nodes
      // and the health service taxonomy.
      $this->setFacility();
      $this->setSystemService();

      if (empty($this->errors) && ($this->isPushable())) {
        // There were no errors gathering data and it is pushable, so proceed.
        $data['nid'] = $this->facilityService->id();
        // Queue item's Unique ID.
        $data['uid'] = "facility_service_{$this->Facility->id()}_{$this->facilityService->id()}";
        $facilityApiId = $this->Facility->hasField('field_facility_locator_api_id') ? $this->Facility->get('field_facility_locator_api_id')->value : NULL;
        $data['endpoint_path'] = ($facilityApiId) ? "/services/va_facilities/v0/facilities/{$facilityApiId}/cms-overlay" : NULL;
        $data['payload'] = $this->getPayload();

        // Only add to queue if payload is not empty.
        // If its empty, it means that there is no new information to send to
        // endpoint.
        if (!empty($data['payload']) && !empty($facilityApiId)) {
          // If bypass_data_check setting is enabled, do not dedupe, just force.
          $dedupe = !$this->shouldBypass();
          $this->postQueue->addToQueue($data, $dedupe);
          // @todo When this is expanded to more than just COVID we may want
          // to remove the messenger as it will be too noisy.
          $message = t('The facility service data for %service_name is being sent to the Facility Locator.', ['%service_name' => $this->facilityService->getTitle()]);
          $this->messenger->addStatus($message);
          return 1;
        }
      }
      elseif (!empty($this->errors) && ($this->isPushable())) {
        // We were supposed to push it, but there was a problem.
        $errors = implode(' ', $this->errors);
        $message = sprintf('Post API: attempted to add a system  NID %d to queue, but ran into errors: %s', $this->facilityService->id(), $errors);
        $this->logger->get('va_gov_post_api')->error($message);

        return 0;
      }
    }
  }

  /**
   * Compose and return payload array for facility service.
   *
   * @return array
   *   Payload array.
   */
  protected function getPayload() {
    // Default payload is an empty array.
    $payload = [];

    if (empty($this->errors) && $this->shouldPush()) {
      $service = new \stdClass();
      $service->name = $this->serviceTerm->getName();
      $service->active = ($this->facilityService->isPublished()) ? TRUE : FALSE;
      $service->description_national = $this->serviceTerm->getDescription();
      $service->description_system = $this->systemService->get('field_body')->value;
      $service->description_facility = $this->facilityService->get('field_body')->value;
      $service->health_service_api_id = $this->serviceTerm->get('field_health_service_api_id')->value;
      $service->appointment_leadin = $this->getAppointmentLeadin();
      $service->appointment_phones = $this->getPhones();
      $service->referral_required = $this->getReferralRequired();
      $service->walk_ins_accepted = $this->getWalkInsAccepted();

      $payload = [
        'detailed_services' => [$service],
      ];
    }

    return $payload;
  }

  /**
   * Assembles the phone data and returns an array of objects.
   *
   * @return array
   *   An array of objects with properties type, label, number, extension.
   */
  protected function getPhones() {
    $assembled_phones = [];
    // Grab phones from the facility service.
    $phones = $this->facilityService->get('field_phone_numbers_paragraph')->referencedEntities();
    if (empty($phones)) {
      // The service has no phones, so use the facility's as fallback.
      $phone_w_ext = $this->Facility->get('field_phone_number')->value;
      // This field may have extension present like 555-555-1212 x 444.
      $phone_split = explode('x', $phone_w_ext);
      $assembledPhone = new \stdClass();
      $assembledPhone->type = 'tel';
      $assembledPhone->label = "Main phone";
      $assembledPhone->number = !empty($phone_split[0]) ? trim($phone_split[0]) : NULL;
      $assembledPhone->extension = !empty($phone_split[1]) ? trim($phone_split[1]) : NULL;
      $assembled_phones[] = $assembledPhone;
    }
    else {
      // Process the phones from the facility health service.
      foreach ($phones as $phone) {
        $assembledPhone = new \stdClass();
        $assembledPhone->type = $phone->get('field_phone_number_type')->value;
        $assembledPhone->label = $phone->get('field_phone_label')->value;
        $assembledPhone->number = $phone->get('field_phone_number')->value;
        $assembledPhone->extension = $phone->get('field_phone_extension')->value;
        $assembled_phones[] = $assembledPhone;
      }
    }

    return $assembled_phones;
  }

  /**
   * Gets the appropriate appointment intro text.
   *
   * @return string
   *   The mapped values of the field.  True, False, not applicable, NULL.
   */
  protected function getAppointmentLeadin() {
    $selection = $this->facilityService->get('field_hservice_appt_intro_select')->value;

    switch ($selection) {
      case 'custom_intro_text':
        $text = $this->facilityService->get('field_hservice_appt_leadin')->value;
        break;

      case 'no_intro_text':
        $text = NULL;
        break;

      case 'default_intro_text':
      default:
        $markupField = $this->facilityService->get('field_hservices_lead_in_default');
        $text = $markupField->getSetting('markup')['value'] ?? NULL;

        break;
    }

    return $text;
  }

  /**
   * Maps and returns the value of referral required.
   *
   * @return string
   *   The mapped values of the field.  True, False, not applicable, NULL.
   */
  protected function getReferralRequired() {
    $raw = $this->facilityService->get('field_referral_required')->value;
    $map = [
      // Value => Return.
      // Lighthouse decided to receive these as strings since non-bool options.
      '0' => 'false',
      '1' => 'true',
      'not_applicable' => 'not applicable',
    ];

    return $map[$raw] ?? NULL;
  }

  /**
   * Maps and returns the value of walk ins accepted.
   *
   * @return string
   *   The mapped values of the field.  True, False, not applicable, NULL.
   */
  protected function getWalkInsAccepted() {
    $raw = $this->facilityService->get('field_walk_ins_accepted')->value;
    $map = [
      // Value => Return.
      // Lighthouse decided to receive these as strings since non-bool options.
      '0' => 'false',
      '1' => 'true',
      'not_applicable' => 'not applicable',
    ];

    return $map[$raw] ?? NULL;
  }

  /**
   * Checks to see if the data checks should be bypassed.
   *
   * @return bool
   *   TRUE if bypass, FALSE if no bypass.
   */
  protected function shouldBypass() {
    return !empty($this->configFactory->get('va_gov_post_api.settings')->get('bypass_data_check'));
  }

  /**
   * Determines if the data says a payload should be assembled and pushed.
   *
   * @return bool
   *   TRUE if should be pushed, FALSE otherwise.
   */
  protected function shouldPush() {
    // Moderation state of what is being saved.
    $moderationState = $this->facilityService->moderation_state->value;
    $isArchived = ($moderationState === 'archived') ? TRUE : FALSE;
    $thisRevisionIsPublished = $this->facilityService->isPublished();
    $defaultRevisionIsPublished = (isset($this->facilityService->original) && ($this->facilityService->original instanceof EntityInterface)) ? (bool) $this->facilityService->original->status->value : (bool) $this->facilityService->status->value;
    $isNew = $this->facilityService->isNew();

    // Case race. First to evaluate to TRUE wins.
    switch (TRUE) {
      case $isNew:
        // A new node, should be pushed to initiate the value.
      case $thisRevisionIsPublished:
        // This revision is published, should be pushed.
      case $isArchived:
        // This node has been archived, got to push to remove it.
      case (!$defaultRevisionIsPublished && !$thisRevisionIsPublished):
        // Draft on node that has not been published, should be pushed.
        $push = TRUE;
        break;

      case ($defaultRevisionIsPublished && !$thisRevisionIsPublished):
        // Draft revision on published node, should not push, even w/bypass.
        $push = FALSE;
        break;

      case ($this->shouldBypass()):
        // Bypass is activated.
        $push = TRUE;
        break;

      default:
        // Anything that makes it this far should not be pushed.
        $push = FALSE;
        break;
    }

    return $push;
  }

  /**
   * Checks to see if this service is slated for pushing.
   */
  private function isPushable() {
    return (!empty($this->serviceTerm) && in_array($this->serviceTerm->id(), $this->servicesToPush));
  }

  /**
   * Load and set the facility node that this service belongs to.
   */
  protected function setFacility() {
    $field = $this->facilityService->get('field_facility_location');
    $facility = (!empty($field)) ? $field->referencedEntities() : NULL;
    if (!empty($facility)) {
      $this->Facility = reset($facility);
    }
    else {
      $this->errors[] = "Unable to load related facility. Field 'field_facility_location' not set.";
    }
  }

  /**
   * Load and set the system health service node that belongs with this service.
   */
  protected function setSystemService() {
    $system_health_service = $this->facilityService->get('field_regional_health_service')->referencedEntities();
    if (!empty($system_health_service)) {
      $this->systemService = reset($system_health_service);
      $this->setServiceTerm();
    }
    else {
      $this->errors[] = "Unable to load system service. Field 'field_regional_health_service' not set.";
    }
  }

  /**
   * Load and set the health service taxonomy term for this service.
   */
  protected function setServiceTerm() {
    $health_service_term_field = $this->systemService->get('field_service_name_and_descripti');
    $health_service_term = (!empty($health_service_term_field)) ? $health_service_term_field->referencedEntities() : NULL;

    if (!empty($health_service_term)) {
      $this->serviceTerm = reset($health_service_term);
    }
    else {
      $this->errors[] = "Unable to load health service term. Field 'field_service_name_and_descripti' not set.";
    }
  }

}
