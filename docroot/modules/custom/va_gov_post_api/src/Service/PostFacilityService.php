<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\FileRepositoryInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\post_api\Service\AddToQueue;
use Drupal\va_gov_lovell\LovellOps;

/**
 * Class PostFacilityService posts specific service info to Lighthouse.
 */
class PostFacilityService extends PostFacilityBase {

  use StringTranslationTrait;

  /**
   * A array of any errors in prepping the data.
   *
   * @var array
   */
  protected $errors = [];

  /**
   * The facility node.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $facility;

  /**
   * The facility service node.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $facilityService;

  /**
   * Core renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

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
   * The services that should be withheld from Lighthouse.
   *
   * The key is for making sense of code, the TID is used for comparison.
   *
   * @var array
   */
  protected $servicesToWithhold = [
    // Key: service name (not used) => Value: TID.
    'Caregiver support' => 48,
    'Mental health care' => 43,
  ];

  /**
   * The service for creating a directory.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The service for creating the log file.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * The name of the log file.
   *
   * @var string
   */
  protected $logFile = "";

  /**
   * Constructs a new PostFacilityBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\post_api\Service\AddToQueue $post_queue
   *   The PostAPI service.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Core renderer.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system service.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   File repository service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_channel_factory,
    MessengerInterface $messenger,
    AddToQueue $post_queue,
    Renderer $renderer,
    FileSystemInterface $file_system,
    FileRepositoryInterface $file_repository
  ) {
    parent::__construct($config_factory, $entity_type_manager, $logger_channel_factory, $messenger, $post_queue);
    $this->renderer = $renderer;
    $this->fileRepository = $file_repository;
    $this->fileSystem = $file_system;
  }

  /**
   * Adds facility service data to Post API queue.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   * @param bool $forcePush
   *   Processing forced by referenced system service.
   *
   * @return int
   *   The count of the number of items queued (1,0).
   */
  public function queueFacilityService(EntityInterface $entity, bool $forcePush = FALSE) {
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
        $data['uid'] = "facility_service_{$this->facility->id()}_{$this->facilityService->id()}";
        $facilityApiId = $this->facility->hasField('field_facility_locator_api_id') ? $this->facility->get('field_facility_locator_api_id')->value : NULL;
        $data['endpoint_path'] = ($facilityApiId) ? "/services/va_facilities/v0/facilities/{$facilityApiId}/cms-overlay" : NULL;
        $data['payload'] = $this->getPayload($forcePush);

        // Only add to queue if payload is not empty.
        // If its empty, it means that there is no new information to send to
        // endpoint.
        if (!empty($data['payload']) && !empty($facilityApiId)) {
          $this->postQueue->addToQueue($data, $this->shouldDedupe());
          if (!empty($data['payload']['detailed_services'][0])
              && $forcePush) {
            try {
              $this->logService($facilityApiId, $data['payload']['detailed_services']['0']->service_api_id);
            }
            catch (\Exception $e) {
              $message = sprintf('VA.gov Post API: Failed to log the service. %s', $e->getMessage());
              $this->loggerChannelFactory->get('va_gov_post_api')->error($message);
            }

          }
          return 1;
        }
      }
      elseif (!empty($this->errors) && ($this->isPushable())) {
        // We were supposed to push it, but there was a problem.
        $errors = implode(' ', $this->errors);
        $message = sprintf('Post API: attempted to add a system  NID %d to queue, but ran into errors: %s', $this->facilityService->id(), $errors);
        $this->loggerChannelFactory->get('va_gov_post_api')->error($message);

        return 0;
      }
    }
  }

  /**
   * Adds facility service data to Post API queue by term.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @return int
   *   The count of the number of items queued.
   */
  public function queueServiceTermRelatedServices(EntityInterface $entity) {
    $queued_count = 0;
    if (($entity->getEntityTypeId() === 'taxonomy_term') && ($entity->bundle() === 'health_care_service_taxonomy')) {
      // Find all VAMC System Health Services referencing this term.
      $query = $this->entityTypeManager->getStorage('node')->getQuery();
      $result = $query->condition('type', 'regional_health_care_service_des')
        ->condition('field_service_name_and_descripti', $entity->id())
        ->condition('status', 1)
        ->accessCheck(FALSE)
        ->execute();

      if (!empty($result)) {
        try {
          $total = count($result);
          $current = 0;

          while ($current < $total) {
            // Run through a batch of 50.
            $nids = array_slice($result, $current, 50, FALSE);

            $system_service_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
            foreach ($system_service_nodes as $node) {
              // Process each VAMC System Health Service using this term.
              $queued_count += $this->queueSystemRelatedServices($node, TRUE);
              $current++;
            }

            $message = sprintf('VA.gov Post API: %s of %d regional_health_care_service_des nodes processed. Queued %s health_care_local_health_service nodes for sync to Lighthouse.', $current, $total, $queued_count);
            $this->loggerChannelFactory->get('va_gov_post_api')->info($message);

          }
        }
        catch (\Exception $e) {
          $message = sprintf('VA.gov Post API: Failed queuing items of type regional_health_care_service_des. %e', $e->getMessage());
          $this->loggerChannelFactory->get('va_gov_post_api')->error($message);
        }
      }
    }

    return $queued_count;
  }

  /**
   * Adds facility service data to Post API queue by system health service.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   * @param bool $forcePush
   *   Processing forced by referenced term.
   *
   * @return int
   *   The count of the number of items queued.
   */
  public function queueSystemRelatedServices(EntityInterface $entity, bool $forcePush = FALSE) {
    $queued_count = 0;
    if (($entity->getEntityTypeId() === 'node') && ($entity->bundle() === 'regional_health_care_service_des')) {
      if ($this->shouldPush($entity, $forcePush)) {
        // Find all VAMC Facility Health Services referencing this node.
        $query = $this->entityTypeManager->getStorage('node')->getQuery();
        $nids = $query->condition('type', 'health_care_local_health_service')
          ->condition('field_regional_health_service', $entity->id())
          ->condition('status', 1)
          ->accessCheck(FALSE)
          ->execute();

        $facility_health_service_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
        foreach ($facility_health_service_nodes as $node) {
          // Process each VAMC Facility Health Service referencing this node.
          $queued_count += $this->queueFacilityService($node, $forcePush);
        }
      }
    }

    return $queued_count;
  }

  /**
   * Compose and return payload array for facility service.
   *
   * @param bool $forcePush
   *   Processing forced by referenced system service.
   *
   * @return array
   *   Payload array.
   */
  protected function getPayload(bool $forcePush = FALSE) {
    // Default payload is an empty array.
    $payload = [];

    if (empty($this->errors) && $this->shouldPush($this->facilityService, $forcePush)) {
      $service = new \stdClass();
      $service->name = $this->serviceTerm->getName();
      $service->active = ($this->facilityService->isPublished()) ? TRUE : FALSE;
      $service->description_national = $this->serviceTerm->getDescription();
      $service->description_system = $this->getProcessedHtmlFromField('field_body');
      $service->service_api_id = $this->serviceTerm->get('field_health_service_api_id')->value;
      $service->appointment_leadin = $this->getAppointmentLeadin();
      $field_phone_numbers_paragraphs = $this->facilityService->get('field_phone_numbers_paragraph')->referencedEntities();
      $service->appointment_phones = $this->getPhones(FALSE, $field_phone_numbers_paragraphs);
      // These three fields are repeated here to support Facilty API V0
      // for Covid-19 Vaccines.
      $service->referral_required = $this->getReferralRequired();
      $service->walk_ins_accepted = $this->getWalkInsAccepted();
      $service->online_scheduling_available = $this->getOnlineSchedulingAvailable();

      $service->service_locations = $this->getServiceLocations();

      $payload = [
        'detailed_services' => [$service],
      ];
    }

    return $payload;
  }

  /**
   * Assembles the phone data and returns an array of phone objects.
   *
   * @param bool $from_facility
   *   Whether to include the phone from the facility.
   * @param array $phone_paragraphs
   *   Optional array of phone paragraphs.
   *
   * @return array
   *   An array of objects with properties type, label, number, extension.
   */
  protected function getPhones($from_facility = FALSE, array $phone_paragraphs = []) {
    $assembled_phones = [];
    if (!empty($phone_paragraphs)) {
      $phones = $phone_paragraphs;
    }

    if ($from_facility) {
      // We need to include the Facility's phone.
      $phone_w_ext = $this->facility->get('field_phone_number')->value;
      // This field may have extension present like 555-555-1212 x 444.
      $phone_split = explode('x', $phone_w_ext);
      $assembledPhone = new \stdClass();
      $assembledPhone->type = 'tel';
      $assembledPhone->label = "Main phone";
      $assembledPhone->number = !empty($phone_split[0]) ? trim($phone_split[0]) : NULL;
      $assembledPhone->extension = !empty($phone_split[1]) ? trim($phone_split[1]) : NULL;
      $assembled_phones[] = $assembledPhone;
    }

    if (!empty($phones)) {
      // Assemble the phones.
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

    return $this->stringNullify($text);
  }

  /**
   * Builds the array of service locations.
   *
   * @return array
   *   An array of 1 or more service location objects.
   */
  protected function getServiceLocations(): array {
    $service_locations = [];
    $field_service_locations = $this->facilityService->get('field_service_location')->referencedEntities();
    $facility_location = new \stdClass();
    $facility_location->office_name = NULL;
    $facility_location->email_contacts = NULL;
    $facility_location->fservice_hours = $this->getServiceHours();
    $facility_location->additional_hours_info = NULL;
    $facility_location->phones = $this->getPhones(TRUE);
    $facility_location->service_location_address = $this->facility->get('field_address');
    if (empty($field_service_locations)) {
      // The service has no locations, so use the facility's as fallback.
      $service_locations[] = $facility_location;
    }
    else {
      // We have some locations.
      foreach ($field_service_locations as $location) {
        $service_location = new \stdClass();
        $field_service_location_address = $location->get('field_service_location_address')->referencedEntities();
        $address_paragraph = reset($field_service_location_address);
        $service_location->office_name = $this->stringNullify($address_paragraph->get('field_clinic_name')->value);
        $service_location->service_address = $this->getServiceAddress($address_paragraph);
        $field_email_contacts = $location->get('field_email_contacts')->referencedEntities();

        $service_location->email_contacts = $this->getEmailContacts($field_email_contacts);
        if ($location->get('field_hours')->value === '0') {
          // Use facility hours.
          $service_location->service_hours = $this->getServiceHours();
        }
        elseif ($location->get('field_hours')->value === '2') {
          // Use location hours.
          $service_location->service_hours = $this->getServiceHours($location->field_office_hours->getValue());
        }
        else {
          // Provide no hours.
          $service_location->service_hours = NULL;
        }

        $service_location->additional_hours_info = $location->get('field_additional_hours_info')->value;
        $use_facility_phone = $location->get('field_use_main_facility_phone')->value;
        $service_location->phones = $this->getPhones($use_facility_phone, $location->get('field_phone')->referencedEntities());
        // These three fields are here for Facilities API V1+
        // They will eventually be part of the CMS service location, but are
        // currently sourced from the facility service node.
        $service_location->referral_required = $this->getReferralRequired();
        $service_location->walk_ins_accepted = $this->getWalkInsAccepted();
        $service_location->online_scheduling_available = $this->getOnlineSchedulingAvailable();

        $service_locations[] = $service_location;
      }
    }

    return $service_locations;
  }

  /**
   * Gets the email addresses from the field data.
   *
   * @param array $field_email_contacts
   *   An array of email_contact paragraphs.
   *
   * @return array
   *   An array of stdClass objects containing label and address.
   */
  protected function getEmailContacts(array $field_email_contacts): array {
    $email_contacts = [];
    if (!empty($field_email_contacts)) {
      foreach ($field_email_contacts as $field_email_contact) {
        $contact = new \stdClass();
        $contact->email_label = $field_email_contact->get('field_email_label')->value;
        $contact->email_address = $field_email_contact->get('field_email_address')->value;

        $email_contacts[] = $contact;
      }
    }

    return $email_contacts;
  }

  /**
   * Pull the address info from an address paragraph.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph|bool $address_paragraph
   *   A drupal paragraph object that should be the address paragraph.
   *
   * @return object
   *   A stdClass object with address elements.
   */
  protected function getServiceAddress(Paragraph | bool $address_paragraph): object {
    $address = new \stdClass();
    if (empty($address_paragraph)) {
      return $address;
    }
    // We made it this far so it must be a paragraph, so declare it.
    /** @var \Drupal\paragraphs\Entity\Paragraph $address_paragraph */
    // Prep the parts of the address not dependent on facility.
    $address->building_name_number = $this->stringNullify($address_paragraph->get('field_building_name_number')->value);
    $address->wing_floor_or_room_number = $this->stringNullify($address_paragraph->get('field_wing_floor_or_room_number')->value);
    if ($address_paragraph->get('field_use_facility_address')->value) {
      // Get info from the facility.
      $field_address = $this->facility->field_address->getValue();
      $use_address = reset($field_address);
    }
    else {
      $field_address = $address_paragraph->field_address->getValue();
      $use_address = reset($field_address);
    }
    $address->address_line1 = $use_address['address_line1'];
    $address->address_line2 = $use_address['address_line2'];
    $address->city = $use_address['locality'];
    $address->state = $use_address['administrative_area'];
    $address->zip_code = $use_address['postal_code'];
    $address->country_code = $use_address['country_code'];

    return $address;
  }

  /**
   * Render html from field and make relative links va.gov specific.
   *
   * @param string $fieldname
   *   The name of the field to retrieve.
   *
   * @return string
   *   Whatever html was found.
   */
  protected function getProcessedHtmlFromField($fieldname) {
    $html = '';
    if (!empty($this->systemService->$fieldname)) {
      $render_array = $this->systemService->$fieldname->view();
      $html = (string) $this->renderer->renderPlain($render_array);
      $html = $this->makeLinksVaGov($html);
    }

    return $html;
  }

  /**
   * Swaps the href for relative links to be https://www.va.gov specific.
   *
   * @param string $html
   *   The html that was passed in, with links' hrefs altered.
   *
   * @return string
   *   Html with no relative links.
   */
  protected function makeLinksVaGov($html) {
    $search_and_replace = [
      // Accounts for pdf files but not images. Images can not be resolved.
      '/sites/default/files/' => '/files/',
      // Accounts for domain addition.
      ' href="/' => ' href="https://www.va.gov/',
    ];
    $search = array_keys($search_and_replace);
    $replace = array_values($search_and_replace);
    $html_with_vagov_links = str_replace($search, $replace, $html);

    return $html_with_vagov_links;
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
   * Maps and returns the value of online scheduling.
   *
   * @return string
   *   The mapped values of the field.  True, False, not applicable, NULL.
   */
  protected function getOnlineSchedulingAvailable() {
    $raw = $this->facilityService->get('field_online_scheduling_availabl')->value;
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
   * Determines if the data says a payload should be assembled and pushed.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   * @param bool $forcePush
   *   Process due to referenced entity updates.
   *
   * @return bool
   *   TRUE if should be pushed, FALSE otherwise.
   */
  protected function shouldPush(EntityInterface $entity, bool $forcePush = FALSE) {
    // Moderation state of what is being saved.
    $moderationState = $entity->moderation_state->value;
    $isArchived = ($moderationState === 'archived') ? TRUE : FALSE;
    $thisRevisionIsPublished = $entity->isPublished();
    $defaultRevisionIsPublished = (isset($entity->original) && ($entity->original instanceof EntityInterface)) ? (bool) $entity->original->status->value : (bool) $entity->status->value;
    $isNew = $entity->isNew();

    // Case race. First to evaluate to TRUE wins.
    switch (TRUE) {
      case LovellOps::isLovellTricareSection($entity):
        // Node is part of the Lovell-Tricare section, do not push.
      case (!$defaultRevisionIsPublished && !$thisRevisionIsPublished):
        // Draft services should not be pushed.
        $push = FALSE;
        break;

      case $forcePush && $thisRevisionIsPublished:
        // Forced push from updates to referenced entity.
      case $isNew:
        // A new node, should be pushed to initiate the value.
      case $thisRevisionIsPublished:
        // This revision is published, should be pushed.
      case $isArchived:
        // This node has been archived, got to push to remove it.
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
    return (!empty($this->serviceTerm) && !in_array($this->serviceTerm->id(), $this->servicesToWithhold));
  }

  /**
   * Load and set the facility node that this service belongs to.
   */
  protected function setFacility() {
    $field = $this->facilityService->get('field_facility_location');
    $facility = (!empty($field)) ? $field->referencedEntities() : NULL;
    if (!empty($facility)) {
      $this->facility = reset($facility);
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

  /**
   * Log service by facility.
   *
   * @param string $facilityApiId
   *   Facility API Id.
   * @param string $facilityService
   *   Facility service.
   */
  protected function logService(string $facilityApiId, string $facilityService) {
    if (empty($this->logFile)) {
      $this->logFile = $this->createLogFile();
    }
    else {
      $log_message = date('Y-m-d H:i:s') . "|{$facilityApiId}|{$facilityService}\n";
      $handle = fopen($this->logFile, "a");
      if ($handle) {
        fwrite($handle, $log_message);
        fclose($handle);
      }
      else {
        $message = sprintf('VA.gov Post API: The log file does not exist. No entry was made for the %s service at %s facility.', $facilityService, $facilityApiId);
        $this->loggerChannelFactory->get('va_gov_post_api')->info($message);
      }
    }

  }

  /**
   * Create a log file.
   */
  protected function createLogFile() {
    $filePath = "";
    $date = date('Y-m-d--H-i');
    $header = 'Time When Added to Log|Facility API ID|Facility Service' . PHP_EOL;
    $directory = 'public://post_api_force_queue';
    $directoryCreated = $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    if ($directoryCreated) {
      $filePath = "{$directory}/services-{$date}.txt";
      $this->fileRepository->writeData($header, $filePath, FileSystemInterface::EXISTS_REPLACE);
      if (file_exists($filePath)) {
        // Tried to create the URL with Url::fromUri($file),
        // but could not get a path from toString().
        $message = sprintf('VA.gov Post API: A log file was created at %s', "/sites/default/files/post_api_force_queue/services-{$date}.txt");
        $this->loggerChannelFactory->get('va_gov_post_api')->info($message);
      }
    }
    return $filePath;
  }

}
