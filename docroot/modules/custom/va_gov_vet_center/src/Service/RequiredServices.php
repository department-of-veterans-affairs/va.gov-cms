<?php

namespace Drupal\va_gov_vet_center\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\TermInterface;

/**
 * Class RequiredServices enforces required services for Vet Centers.
 */
class RequiredServices {
  use StringTranslationTrait;

  /**
   * The entity type manager.
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
   * Queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * An array of required service taxonomy term objects.
   *
   * @var array
   */
  protected $requiredServices;

  /**
   * Constructs a new RequiredServices object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger factory service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_channel_factory,
    MessengerInterface $messenger,
    QueueFactory $queue
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannelFactory = $logger_channel_factory;
    $this->messenger = $messenger;
    $this->queueFactory = $queue;
  }

  /**
   * Add a new queue item for this term/facility combination.
   *
   * @param \Drupal\Core\Entity\EntityInterface $service_term
   *   The taxonomy term for the new service item.
   * @param \Drupal\Core\Entity\EntityInterface $facility_node
   *   The vet center (facility) node for the new service item.
   * @param string $log_message
   *   Text to use as the revision log message for the new service item.
   */
  public function addService(EntityInterface $service_term, EntityInterface $facility_node, string $log_message = '') {
    // Add a queue item to be processed on next cron run.
    $service_queue = $this->queueFactory->get('cron_required_service');

    $item = new \stdClass();
    // Unique queue id to use for deduping.
    $item->quid = "{$service_term->id()}-{$facility_node->id()}";
    $item->facility_id = $facility_node->id();
    /** @var \Drupal\node\NodeInterface $facility_node */
    $item->published = $facility_node->isPublished();
    $item->section_id = $facility_node->field_administration->target_id;
    $item->moderation_state = $facility_node->get('moderation_state')->value;
    $item->term_id = $service_term->id();
    $item->log_message = $log_message;
    $this->dedupeQueue($service_queue, $item->quid);
    $service_queue->createItem($item);
  }

  /**
   * Add all required services for a provided facility (vet center).
   *
   * @param \Drupal\Core\Entity\EntityInterface $facility_node
   *   The vet center (facility) node for the new service items.
   */
  public function addRequiredServicesByFacility(EntityInterface $facility_node) {
    /** @var \Drupal\node\NodeInterface $facility_node */
    if ($facility_node->bundle() == 'vet_center' && is_null($facility_node->original)) {
      // Be sure a given facility has all required services.
      $required_services = $this->getRequiredServices();
      $log_message = 'Created from new parent facility.';

      foreach ($required_services as $required_service) {
        $this->addService($required_service, $facility_node, $log_message);
      }
      $vars = [
        '%count' => count($required_services),
        '%title' => $facility_node->getTitle(),
      ];
      // This will be done during migration so log it.
      $message = $this->t('%count services were queued to be added to %title', $vars);
      $this->loggerChannelFactory->get('va_gov_vet_center')->info($message);
    }
  }

  /**
   * Add all required services for a provided service term.
   *
   * @param \Drupal\Core\Entity\EntityInterface $service_term
   *   The taxonomy term for the new service items.
   */
  public function addRequiredServicesByTerm(EntityInterface $service_term) {
    /** @var \Drupal\taxonomy\TermInterface $service_term */
    if ($service_term->bundle() === 'health_care_service_taxonomy'
      && $service_term->hasField('field_vet_center_required_servic')) {
      $previously_required = isset($service_term->original) ? $service_term->original->get('field_vet_center_required_servic')->value : 0;
      if ($service_term->get('field_vet_center_required_servic')->value && !$previously_required) {
        $node_storage = $this->entityTypeManager->getStorage('node');

        // Get all the vet center node ids.
        $vet_center_query = $node_storage->getQuery();
        $vet_center_nids = $vet_center_query
          ->condition('type', 'vet_center')
          ->accessCheck(FALSE)
          ->execute();

        // Get all the facility health service node ids using this term.
        $term_id = $service_term->id();
        $service_query = $node_storage->getQuery();
        $facility_service_nids = $service_query
          ->condition('type', 'vet_center_facility_health_servi')
          ->condition('field_service_name_and_descripti.target_id', $term_id)
          ->accessCheck(FALSE)
          ->execute();

        // Load all the facility health service nodes.
        $facility_services = $node_storage->loadMultiple($facility_service_nids);
        $serviced_nids = [];

        // Get the id for the vet center referenced in each facility service.
        foreach ($facility_services as $facility_service) {
          /** @var \Drupal\node\NodeInterface $facility_service */
          if ($facility_service->hasField('field_office') && isset($facility_service->get('field_office')->entity)) {
            $serviced_nids[] = $facility_service->get('field_office')->entity->id();
          }
        }

        // Remove the vet centers where this service is already present.
        $missing_nids = array_diff($vet_center_nids, $serviced_nids);
        $log_message = 'Created from required VHA health service term.';

        // Add each item to the queue for creation.
        $vet_centers = $node_storage->loadMultiple($missing_nids);
        foreach ($vet_centers as $vet_center) {
          $this->addService($service_term, $vet_center, $log_message);
        }

        $vars = [
          '%count' => count($vet_centers),
          '%name' => $service_term->get('name')->value,
        ];
        $message = $this->t('%count %name services were queued to be added to Vet Centers.', $vars);
        $this->loggerChannelFactory->get('va_gov_vet_center')->info($message);
        $this->messenger->addStatus($message);
      }
    }
  }

  /**
   * Retrieve an array of required health care service taxonomy term entities.
   *
   * @return array
   *   An array of required taxonomy term (service) entities.
   */
  public function getRequiredServices() {
    if (empty($this->requiredServices)) {
      $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
      $term_query = $term_storage->getQuery();

      // Return an array of all VHA health service terms.
      $term_ids = $term_query
        ->condition('vid', 'health_care_service_taxonomy')
        ->accessCheck(FALSE)
        ->execute();
      $service_terms = $term_storage->loadMultiple($term_ids);
      $required_services = [];

      // Create a filtered array containing only required services.
      foreach ($service_terms as $service_term) {
        if ($this->isRequiredService($service_term)) {
          $required_services[] = $service_term;
        }
      }

      $this->requiredServices = $required_services;
    }

    return $this->requiredServices;
  }

  /**
   * Determine whether a provided service term is required or not.
   *
   * @param \Drupal\taxonomy\TermInterface $service_term
   *   The taxonomy term to be evaluated.
   *
   * @return bool
   *   TRUE if required. FALSE otherwise.
   */
  public function isRequiredService(TermInterface $service_term) {
    // Check a service to see if it is required.
    $required = $service_term->get('field_vet_center_required_servic')->value;

    return $required;
  }

  /**
   * Determine whether a provided service exists for a provided facility.
   *
   * @param \Drupal\Core\Entity\EntityInterface $service_term
   *   The taxonomy term to be evaluated.
   * @param \Drupal\Core\Entity\EntityInterface $facility_node
   *   The vet center (facility) node to be evaluated.
   *
   * @return bool
   *   TRUE if provided service exists for provided facility. FALSE otherwise.
   */
  public function hasService(EntityInterface $service_term, EntityInterface $facility_node) {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $node_query = $node_storage->getQuery();

    // Check to see if a given facility has a given service.
    $term_id = $service_term->id();
    $facility_id = $facility_node->id();
    $facility_service_nids = $node_query
      ->condition('type', 'vet_center_facility_health_servi')
      ->condition('field_office.target_id', $facility_id)
      ->condition('field_service_name_and_descripti.target_id', $term_id)
      ->accessCheck(FALSE)
      ->execute();

    $has_service = (count($facility_service_nids) > 0 ? TRUE : FALSE);

    return $has_service;
  }

  /**
   * Remove any queued items with the same $quid as this one.
   *
   * @param \Drupal\Core\Queue\QueueInterface $queue
   *   A queue object.
   * @param string $quid
   *   Unique id of "tid-nid" to dedupe against.
   */
  private function dedupeQueue(QueueInterface $queue, $quid) {
    $item_count = $queue->numberOfItems();
    $removed_count = 0;
    // The queue will keep grabbing the same item after it is released, so
    // we need to grab them all and mass release them.
    $queued_items_to_release = [];
    for ($i = 0; $i < $item_count; $i++) {
      // Claim queued item.
      $queued_item = $queue->claimItem();
      if ($queued_item && $quid) {
        try {
          // Check if it has the same unique id as the
          // one we will be adding.
          if ($quid === $queued_item->data->quid) {
            // Item uid matches the new one - delete it.
            $queue->deleteItem($queued_item);
            $removed_count++;
          }
          else {
            // It is not a duplicate - release it back to the queue.
            $queued_items_to_release[$queued_item->item_id] = $queued_item;
          }
        }
        catch (SuspendQueueException $e) {
          // In case of Exception - release the item that the worker could
          // not process. It will be processed in the next batch.
          $queued_items_to_release[$queued_item->item_id] = $queued_item;
          continue;
        }
      }
    }

    // Release the non-duplicates.
    foreach ($queued_items_to_release as $queued_items_to_release) {
      $queue->releaseItem($queued_items_to_release);
    }
  }

}
