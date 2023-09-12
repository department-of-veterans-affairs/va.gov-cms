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
use Drupal\post_api\Service\AddToQueue;
use Drupal\va_gov_lovell\LovellOps;

/**
 * Class PostFacilityService posts specific service info to Lighthouse.
 */
abstract class PostFacilityServiceBase extends PostFacilityBase {

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
   * Get the facility phone number.
   *
   * @return array
   *   Facility phone information.
   */
  protected function getFacilityPhone() {
    $phone_w_ext = $this->facility->get('field_phone_number')->value;
    // This field may have extension present like 555-555-1212 x 444.
    $phone_split = explode('x', $phone_w_ext);
    $assembledPhone = new \stdClass();
    $assembledPhone->type = 'tel';
    $assembledPhone->label = "Main phone";
    $assembledPhone->number = !empty($phone_split[0]) ? trim($phone_split[0]) : NULL;
    $assembledPhone->extension = !empty($phone_split[1]) ? trim($phone_split[1]) : NULL;
    return $assembledPhone;
  }

  /**
   * Get the facility address.
   */
  protected function getFacilityAddress(object &$address, array $use_address) {
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
   * @param string $serviceType
   *   The type of facility service.
   *   Examples: 'systemService' or 'facilityService'.
   * @param string $fieldName
   *   The name of the field to retrieve.
   *
   * @return string
   *   Whatever html was found.
   */
  protected function getProcessedHtmlFromField(string $serviceType, string $fieldName) {
    $html = '';
    if (!empty($this->{$serviceType}->$fieldName)) {
      $render_array = $this->{$serviceType}->$fieldName->view();
      $html = (string) $this->renderer->renderPlain($render_array);
      $html = $this->makeLinksVaGov($html);
    }
    else {
      $message = sprintf('VA.gov Post API: The %s field does not exist in the %s service.', $fieldName, $serviceType);
      $this->loggerChannelFactory->get('va_gov_post_api')->error($message);

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
    $isArchived = ($moderationState === 'archived');
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
  protected function isPushable() {
    return (!empty($this->serviceTerm) && !in_array($this->serviceTerm->id(), $this->servicesToWithhold));
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
    $filePath = $this->getLog();
    $log_message = date('Y-m-d H:i:s') . "|{$facilityApiId}|{$facilityService}\n";
    $handle = fopen($filePath, "a");
    if ($handle) {
      fwrite($handle, $log_message);
      fclose($handle);
    }
    else {
      $message = sprintf('VA.gov Post API: The log file does not exist. No entry was made for the %s service at %s facility.', $facilityService, $facilityApiId);
      $this->loggerChannelFactory->get('va_gov_post_api')->info($message);
    }
  }

  /**
   * Gets the services log.
   *
   * @return string
   *   The path to the log file.
   */
  protected function getLog() {
    if (empty($this->logFile)) {
      $this->logFile = $this->createLogFile();
    }
    return $this->logFile;
  }

  /**
   * Create a log file.
   *
   * @return string
   *   The path to the log file.
   */
  protected function createLogFile() {
    $filePath = "";
    $date = date('Y-m-d--H-i-s');
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
