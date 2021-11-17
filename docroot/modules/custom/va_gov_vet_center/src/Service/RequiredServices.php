<?php

namespace Drupal\va_gov_vet_center\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class RequiredServices enforces required services for Vet Centers.
 */
class RequiredServices {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The query factory used to construct all queries in the test.
   *
   * @var \Drupal\Core\Config\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a new RequiredServices object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Add a new queue item for this term/facility combination.
   *
   * @param \Drupal\Core\Entity\EntityInterface $service_term
   *   The taxonomy term for the new service item.
   * @param \Drupal\Core\Entity\EntityInterface $facility_node
   *   The vet center (facility) node for the new service item.
   */
  public function addService(EntityInterface $service_term, EntityInterface $facility_node) {
    // Add a queue item to be processed on next cron run.
    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('cron_required_service');

    $item = new \stdClass();
    $item->facility_id = $facility_node->id();
    $item->term_id = $service_term->id();
    $queue->createItem($item);
  }

  /**
   * Add all required services for a provided facility (vet center).
   *
   * @param \Drupal\Core\Entity\EntityInterface $facility_node
   *   The vet center (facility) node for the new service items.
   */
  public function addServicesByFacility(EntityInterface $facility_node) {
    // Be sure a given facility has all required services.
    $required_services = $this->getRequiredServices();

    foreach ($required_services as $required_service) {
      $this->addService($required_service, $facility_node);
    }
  }

  /**
   * Add all required services for a provided service term.
   *
   * @param \Drupal\Core\Entity\EntityInterface $service_term
   *   The taxonomy term for the new service items.
   */
  public function addServicesByTerm(EntityInterface $service_term) {
    $term_id = $service_term->id();

    // Get all the vet center node ids.
    $vet_center_nids = \Drupal::entityQuery('node')
      ->condition('type', 'vet_center')
      ->execute();

    // Get all the facility health service node ids using this term.
    $facility_service_nids = \Drupal::entityQuery('node')
      ->condition('type', 'vet_center_facility_health_servi')
      ->condition('field_service_name_and_descripti.target_id', $term_id)
      ->execute();

    // Load all the facility health service nodes.
    $storage_handler = $this->entityTypeManager->getStorage('node');
    $facility_services = $storage_handler->loadMultiple($facility_service_nids);
    $serviced_nids = [];

    // Get the id for the vet center referenced in each facility service.
    foreach ($facility_services as $facility_service) {
      if ($facility_service->hasField('field_office')) {
        $serviced_nids[] = $facility_service->get('field_office')->entity->id();
      }
    }

    // Remove the vet centers where this service is already present.
    $missing_nids = array_diff($vet_center_nids, $serviced_nids);

    // Add each item to the queue for creation.
    foreach ($missing_nids as $missing_nid) {
      $vet_center = $storage_handler->load($missing_nid);
      $this->addService($service_term, $vet_center);
    }
  }

  /**
   * Retrieve an array of required health care service taxonomy term entities.
   *
   * @return array
   *   An array of required taxonomy term (service) entities.
   */
  public function getRequiredServices() {
    // Return a list of required services.
    $term_ids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'health_care_service_taxonomy')
      ->execute();
    $storage_handler = $this->entityTypeManager->getStorage('taxonomy_term');
    $service_terms = $storage_handler->loadMultiple($term_ids);
    $required_services = [];

    // Unset any items in the array that aren't required.
    foreach ($service_terms as $service_term) {
      if ($this->isRequiredService($service_term)) {
        $required_services[] = $service_term;
      }
    }

    return $required_services;
  }

  /**
   * Determine whether a provided service term is required or not.
   *
   * @param \Drupal\Core\Entity\EntityInterface $service_term
   *   The taxonomy term to be evaluated.
   *
   * @return bool
   *   Is this term is required or not.
   */
  public function isRequiredService(EntityInterface $service_term) {
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
   *   Does the provided service exist for the provided facility.
   */
  protected function hasService(EntityInterface $service_term, EntityInterface $facility_node) {
    // Check to see if a given facility has a given service.
    $term_id = $service_term->id();
    $facility_id = $facility_node->id();
    $facility_service_nids = \Drupal::entityQuery('node')
      ->condition('type', 'vet_center_facility_health_servi')
      ->condition('field_office.target_id', $facility_id)
      ->condition('field_service_name_and_descripti.target_id', $term_id)
      ->execute();

    $has_service = (count($facility_service_nids) > 0 ? TRUE : FALSE);

    return $has_service;
  }

}
