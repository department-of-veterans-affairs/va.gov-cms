<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\office_hours\OfficeHoursDateHelper;
use Drupal\post_api\Service\AddToQueue;

/**
 * Class PostFacilityService posts specific service info to Lighthouse.
 */
abstract class PostFacilityBase {
  use StringTranslationTrait;

  const STATE_ARCHIVED = 'archived';
  const STATE_DRAFT = 'draft';
  const STATE_PUBLISHED = 'published';

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
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannelFactory;

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
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_channel_factory, MessengerInterface $messenger, AddToQueue $post_queue) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannelFactory = $logger_channel_factory;
    $this->messenger = $messenger;
    $this->postQueue = $post_queue;
  }

  /**
   * Checks to see if the data checks should be bypassed.
   *
   * @return bool
   *   TRUE if bypass, FALSE if no bypass.
   */
  protected function shouldBypass() : bool {
    return !empty($this->configFactory->get('va_gov_post_api.settings')->get('bypass_data_check'));
  }

  /**
   * Checks to see if logging should be enabled.
   *
   * @return bool
   *   TRUE if logging, FALSE if not logging.
   */
  protected function shouldLog() : bool {
    return !empty($this->configFactory->get('va_gov_post_api.settings')->get('enable_logging'));
  }

  /**
   * Checks to see if the post queueing should dedupe.
   *
   * @return bool
   *   TRUE if deduping, FALSE otherwise.
   */
  protected function shouldDedupe() : bool {
    // If bypass_data_check setting is enabled, do not dedupe..
    return !$this->shouldBypass();
  }

  /**
   * Get operating status details, shortened as necessary.
   *
   * @return string
   *   Details of operating status.
   */
  protected function getOperatingStatusMoreInfoShort() : ?string {
    $operatingStatusMoreInfo = $this->facilityNode->get('field_operating_status_more_info')->value;
    if ($operatingStatusMoreInfo) {
      $operatingStatusMoreInfo = $this->facilityNode->get('field_operating_status_more_info')->value;
      $operatingStatusMoreInfoJson = json_encode($this->facilityNode->get('field_operating_status_more_info')->value);
      $operatingStatusMoreInfoLength = mb_strlen($operatingStatusMoreInfoJson);
      // 300 is the character limit for field_operating_status_facility
      // let's do our best to trim this down if we need to
      if ($operatingStatusMoreInfoLength > 300) {
        $operatingStatusMoreInfo = str_replace('&nbsp;', ' ', $operatingStatusMoreInfo);
        $operatingStatusMoreInfo = trim($operatingStatusMoreInfo);
        $operatingStatusMoreInfo = preg_replace("/(\r?\n|\r)+/", " ", $operatingStatusMoreInfo);
      }
    }
    return $operatingStatusMoreInfo;
  }

  /**
   * Get address from an address field.
   *
   * @param mixed $addresses_field
   *   The field value of the address field.
   *
   * @return array|object|null
   *   Array of addresses if there are multiple. Object if 1, NULL if none.
   */
  protected function getAddresses($addresses_field) {
    $return_addresses = NULL;
    $addresses = $addresses_field->getValue();
    if (!empty($addresses) && is_array($addresses)) {
      $return_addresses = [];
      foreach ($addresses as $address) {
        if (!empty($address)) {
          $return_address = new \stdClass();
          $return_address->address_organization = $address['organization'] ?? '';
          $return_address->address_line1 = $address['address_line1'] ?? '';
          $return_address->address_line2 = $address['address_line2'] ?? '';
          $return_address->city = $address['locality'] ?? '';
          $return_address->state = $address['administrative_area'] ?? '';
          $return_address->country_code = $address['country_code'] ?? '';
          $return_address->zip_code = $address['postal_code'] ?? '';
          $return_addresses[] = $return_address;
        }
      }
    }
    // If there are more than one, return them in an array.
    return (count($return_addresses) > 1) ? $return_addresses : $return_addresses[0];
  }

  /**
   * Gets the service hours with fallback to facility.
   *
   * @param array $office_hours
   *   Office hours values to extract days from.
   *
   * @return object
   *   An object of days of the week with hours strings.
   */
  protected function getServiceHours(array $office_hours = []): object {
    if (empty($office_hours)) {
      $office_hours = $this->facility->get('field_office_hours')->getValue();
    }
    $hours = new \stdClass();
    $hours->monday = $this->getDay(0, $office_hours);
    $hours->tuesday = $this->getDay(1, $office_hours);
    $hours->wednesday = $this->getDay(2, $office_hours);
    $hours->thursday = $this->getDay(3, $office_hours);
    $hours->friday = $this->getDay(4, $office_hours);
    $hours->saturday = $this->getDay(5, $office_hours);
    $hours->sunday = $this->getDay(6, $office_hours);

    return $hours;
  }

  /**
   * Gets the string for a given day.
   *
   * @param int $day_num
   *   Number 0-6 specifying which day to return.
   * @param array $days
   *   The array of days data.
   *
   * @return string
   *   a sting made up of start and end times with comment, or just comment.
   */
  protected function getDay($day_num, array $days): string {
    $start = (!empty($days[$day_num]['starthours'])) ? OfficeHoursDateHelper::format($days[$day_num]['starthours'], 'h:i a') : '';
    $end = (!empty($days[$day_num]['endhours'])) ? OfficeHoursDateHelper::format($days[$day_num]['endhours'], 'h:i a') : '';
    // Make sure there is no end if there is no start. Data error.
    $end = empty($start) ? '' : $end;
    $start = $this->normalizeTime($start);
    $end = $this->normalizeTime($end);
    $comment = $days[$day_num]['comment'] ?? '';
    $comment = $this->normalizeHoursComment($comment);
    if (!empty($start) && !empty($end) && !empty($comment)) {
      $day_entry = "{$start} to {$end} {$comment}";
    }
    elseif (!empty($start) && empty($end) && !empty($comment)) {
      $day_entry = "{$start} to {$comment}";
    }
    elseif (!empty($start) && !empty($end) && empty($comment)) {
      $day_entry = "{$start} to {$end}";
    }
    else {
      $day_entry = $comment;
    }

    return trim($day_entry);
  }

  /**
   * Perform processes on a comment to make it normal.
   *
   * @param string $comment
   *   A comment to normalize.
   *
   * @return string
   *   A normalized comment.
   */
  protected function normalizeHoursComment(string $comment): string {
    $comment = trim($comment);
    // There will be more processes coming here.
    return $comment;
  }

  /**
   * Perform alterations to a time string to make normal.
   *
   * @param string $time
   *   A formatted time string.
   *
   * @return string
   *   The normalized string.
   */
  protected function normalizeTime(string $time): string {
    $time = trim($time);
    // Make am pm follow design.va.gov.
    $replacements = [
      'am' => 'a.m.',
      'AM' => 'a.m.',
      'A.M.' => 'a.m.',
      'pm' => 'p.m.',
      'PM' => 'p.m.',
      'P.M.' => 'p.m.',
    ];
    $time = str_replace(array_keys($replacements), array_values($replacements), $time);
    $time = $this->midnightNoonify($time);
    return $time;
  }

  /**
   * Convert noon or midnight to those terms.
   *
   * @param string $time
   *   A time including am or pm.
   *
   * @return string
   *   The time passed in or 'noon' or 'midnight'.
   */
  protected function midnightNoonify(string $time): string {
    $time = trim($time);
    if ($time === '12:00 am' || $time === '12:00 a.m.') {
      $time = 'midnight';
    }
    elseif ($time === '12:00 pm' || $time === '12:00 p.m.') {
      $time = 'noon';
    }
    return $time;
  }

  /**
   * Converts a string to an html id the same way content-build does.
   *
   * @param string $string
   *   A string to convert to an id.
   *
   * @return string
   *   A string matching what the FE will do to make an html id.
   *
   * @see https://github.com/department-of-veterans-affairs/content-build/blob/main/src/site/stages/build/plugins/modify-dom/add-id-to-subheadings.js#L9-L17
   */
  public function getFrontEndFragment($string = ''): string {
    $string = trim($string);
    $string = mb_strtolower($string);
    $string = preg_replace('/[^\w\- ]+/', '', $string);
    $string = preg_replace('/\s/', '-', $string);
    $string = preg_replace('/-+$/', '', $string);
    $string = mb_substr($string, 0, 30);
    $string = (!empty($string)) ? "#{$string}" : '';

    return $string;
  }

  /**
   * Gets the facility locations page with name fragment of facility.
   *
   * @param Drupal\node\NodeInterface $facility
   *   The facility whose parent needs a link.
   *
   * @return string|null
   *   String of the url of the parent's location page or NULL if no parent.
   */
  public function getParentLocationsPageUrl(NodeInterface $facility): string | null {
    $url = NULL;
    if ($facility->hasField('field_office')) {
      $referenced_entities = $facility->get('field_office')->referencedEntities();
      if (!empty($referenced_entities[0])) {
        $parentFacility = $referenced_entities[0];
        $parent_slug = $parentFacility->toUrl()->toString();
        $name_fragment = $this->getFrontEndFragment($facility->getTitle());
        $url = "https://www.va.gov{$parent_slug}/locations/{$name_fragment}";
      }
    }
    return $url;
  }

  /**
   * Converts any empty string to NULL.
   *
   * @param string|null $string
   *   Any possible value to evaluate.
   *
   * @return string|null
   *   String if the value is not empty, NULL otherwise.
   */
  protected function stringNullify($string) {
    return (empty($string)) ? NULL : $string;
  }

  /**
   * Decides what to use as the previous/default revision and returns it.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node we need to find a default revision for.
   *
   * @return \Drupal\node\NodeInterface
   *   The node.
   */
  public static function getDefaultRevision(NodeInterface $entity) : NodeInterface {
    $hasOriginal = isset($entity->original) && ($entity->original instanceof EntityInterface);
    if ($entity->isNew()) {
      // There is no previous revision but to make comparison easier, set
      // the current node as the default.
      $defaultRevision = $entity;
    }
    elseif (($entity->get('moderation_state')->value === self::STATE_PUBLISHED || !$entity->isPublished()) && $hasOriginal) {
      // If it has never been published we just want the last save.
      // If the node is published, loading the default is an exact copy,
      // because the save already happened. Switch to using original.
      $defaultRevision = $entity->original;
    }
    else {
      // As a static function this can no use Dependency Injection.
      $defaultRevision = \Drupal::service('entity_type.manager')->getStorage('node')->load($entity->id());
    }

    return $defaultRevision;
  }

  /**
   * Checks if the value of the field on the node changed.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node we need to compare.
   * @param \Drupal\node\NodeInterface $revision
   *   The revision we are comparing to.
   * @param string $field_name
   *   The machine name of the field to check on.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  protected function changedValue(NodeInterface $node, NodeInterface $revision, $field_name): bool {
    return $this->fieldsHaveChanges($node, $revision, [$field_name]);
  }

  /**
   * Checks if the target_id of the field on the node changed.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node we need to compare.
   * @param \Drupal\node\NodeInterface $revision
   *   The revision we are comparing to.
   * @param string $field_name
   *   The machine name of the field to check on.
   *
   * @return bool
   *   TRUE if the value changed.  FALSE otherwise.
   */
  protected function changedTarget(NodeInterface $node, NodeInterface $revision, $field_name): bool {
    if ($node->hasField($field_name)) {
      $value = $node->get($field_name)->target_id;
      $original_value = $revision->get($field_name)->target_id;

      return $value !== $original_value;
    }
    return FALSE;
  }

  /**
   * Looks for changes in all identified fields.
   *
   * @param \Drupal\node\NodeInterface $current_revision
   *   The current revision.
   * @param \Drupal\node\NodeInterface $default_revision
   *   The default revision.
   * @param array $field_names
   *   An array of drupal field machine names.
   *
   * @return bool
   *   TRUE if there have been changes, FALSE otherwise.
   */
  protected function fieldsHaveChanges(NodeInterface $current_revision, NodeInterface $default_revision, array $field_names): bool {
    $current_revision_values = [];
    $default_revision_values = [];
    $field_reference_types = [
      'entity_reference',
      'entity_reference_revisions',
    ];
    foreach ($field_names as $field_name) {
      if ($current_revision->hasField($field_name)) {
        if (in_array($current_revision->get($field_name)->getFieldDefinition()->getType(), $field_reference_types)) {
          // It is a reference; we need to get the target.
          $current_revision_values[$field_name] = ($current_revision->hasField($field_name)) ? $current_revision->get($field_name)->target_id : NULL;
          $default_revision_values[$field_name] = ($default_revision->hasField($field_name)) ? $default_revision->get($field_name)->target_id : NULL;
        }
        else {
          // It is a normal field, use values.
          $current_revision_values[$field_name] = ($current_revision->hasField($field_name)) ? $current_revision->get($field_name)->value : NULL;
          $default_revision_values[$field_name] = ($default_revision->hasField($field_name)) ? $default_revision->get($field_name)->value : NULL;
        }
      }
    }
    return $current_revision_values == $default_revision_values;
  }

  /**
   * Removes values from anything that should not be kept in state.
   */
  protected function nuke() : void {
    $this->statusToPush = NULL;
    $this->additionalInfoToPush = NULL;
    unset($this->facilityNode);
  }

}
