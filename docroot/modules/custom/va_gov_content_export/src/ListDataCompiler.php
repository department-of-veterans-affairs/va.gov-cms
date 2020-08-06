<?php

namespace Drupal\va_gov_content_export;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Class ListDataCompiler.
 *
 * @package Drupal\va_gov_content_export
 */
class ListDataCompiler {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * An array of entity types that should be treated as lists.
   *
   * Keyed by content type, values: array of fields pointing to content type.
   *
   * @var array
   *
   * @todo It would be nice to pull these dynamically rather than hard code.
   * However that would need to be cached in order to make this faster.
   */
  protected static $listEntityTypes = [
    'event_listing' => ['field_listing'],
    'health_care_local_facility_servi' => ['field_location_services'],
    'health_care_local_facility' => ['field_facility_location'],
    'health_care_local_health_service' => ['field_local_health_care_service_'],
    'health_care_region_page' => ['field_region_page'],
    'leadership_listing' => ['field_office'],
    'office' => ['field_office'],
    'person_profile' => ['field_leadership'],
    'press_releases_listing' => ['field_listing'],
    'publication_listing' => ['field_listing'],
    'regional_health_care_service_des' => ['field_regional_health_service', 'field_clinical_health_services'],
    'story_listing' => ['field_listing'],
    // Should be a list, but can not find anything that points to this.
    // 'health_services_listing',
    // 'locations_listing',.
  ];

  /**
   * An array of field names to treat as a reference to a list.
   *
   * @var array
   */
  protected $listingFields = [];

  /**
   * ListDataCompiler constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->extractListingFields();
  }

  /**
   * Extract field names from $this->listEntityTypes into $this->listingFields.
   */
  protected function extractListingFields() {
    foreach (self::$listEntityTypes as $entityType => $listingFieldNames) {
      foreach ($listingFieldNames as $listingFieldName) {
        if (!in_array($listingFieldName, $this->listingFields)) {
          $this->listingFields[] = $listingFieldName;
        }
      }

    }
  }

  /**
   * Update related reverse entity reference lists.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity which may reference lists.
   * @param \Drupal\va_gov_content_export\TomeExporter $tomeExporter
   *   The tome exporter.
   *
   * @todo This implementation creates one risk of failure in that, any list
   * item that has been moved from one list to another, will not know to remove
   * it from the original list.
   * https://github.com/department-of-veterans-affairs/va.gov-cms/issues/2464 .
   */
  public function updateReverseEntityReferenceLists(ContentEntityInterface $entity, TomeExporter $tomeExporter) {
    $entityReferenceTargetNids = $this->getListNids($entity);
    $entityReferenceTargetNidsToUpdateLater = [];
    $targetNodesToUpdateLater = [];
    if (!empty($entityReferenceTargetNids)) {
      // Process each list this entity should appear in.
      foreach ($entityReferenceTargetNids as $listNid => $listFieldNames) {
        $entityReferenceTargetNidsToUpdateLater[] = $this->updateList($entity, $listNid, $listFieldNames);
      }
      $entityReferenceTargetNidsToUpdateLater = array_filter($entityReferenceTargetNidsToUpdateLater);
    }
    if (!empty($entityReferenceTargetNidsToUpdateLater)) {
      // @todo Add a check to bypass this if the bulk export is running.
      // https://github.com/ ... /va.gov-cms/issues/2481.
      // Load the list entities that need to be exported.
      $targetNodesToUpdateLater = $this->entityTypeManager->getStorage('node')
        ->loadMultiple($entityReferenceTargetNidsToUpdateLater);
      // Export the lists to have their reverse field list updated.
      foreach ($targetNodesToUpdateLater as $targetNodeToUpdateLater) {
        // Update the list export json.
        $tomeExporter->exportContent($targetNodeToUpdateLater);
      }
    }
  }

  /**
   * Update a list to add the list data as ->reverse_fieldname.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity is a list or in a list.
   * @param int $listNid
   *   The node id of ths list this entity belongs in.
   * @param array $listFieldNames
   *   The field machine names to use as the reverse entity reference.
   *
   * @return int
   *   The node id of a list to be updated.
   */
  public function updateList(ContentEntityInterface $entity, $listNid, array $listFieldNames) : int {
    // Check if $entity is the list, or if we need to get the list.
    if ($this->isList($entity) && ((int) $entity->id() === $listNid)) {
      // Add the lists to the list entity.
      $entity->values['reverse_entity_references'] = [];
      foreach ($listFieldNames as $listFieldName) {
        $listNodes = $this->queryListItemNodes($listNid, $listFieldName);
        $list = $this->buildReverseEntityReferenceList($listNodes);
        $entity->values['reverse_entity_references']["reverse_{$listFieldName}"] = $list;
      }
    }
    else {
      return $listNid;
    }

    return FALSE;

  }

  /**
   * Check whether the entity is a list.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check for being a list.
   *
   * @return bool
   *   True is the entity is one known to be a list.
   */
  protected function isList(ContentEntityInterface $entity) : bool {
    if (($entity instanceof NodeInterface) && $entity->getEntityTypeId() === 'node') {
      if (array_key_exists($entity->bundle(), static::$listEntityTypes)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Getter for the list nids related to this entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check.
   *
   * @return array
   *   NodeId and fieldname pairs, one for each list the entity belongs in.
   */
  protected function getListNids(ContentEntityInterface $entity) {
    $listNids = [];
    if ($this->isList($entity)) {
      // This is a list so just return the nid.
      $listNids[$entity->id()] = static::$listEntityTypes[$entity->bundle()];
    }
    else {
      // Look to see if it has any listing fields.
      foreach ($this->listingFields as $listingField) {
        if ($entity->hasField($listingField)) {
          // It has a field that is a reference to a list.
          $lists = $entity->$listingField;
          foreach ($lists as $list) {
            $listNids[$list->target_id][] = $listingField;
          }
        }
      }
    }

    return $listNids;
  }

  /**
   * Query for all the published nodes that belong in this list.
   *
   * @param int $listNid
   *   The node id of the list.
   * @param string $fieldName
   *   The machine name of the field to lookup.
   *
   * @return array
   *   An array of nodes that belong in the list.
   */
  protected function queryListItemNodes($listNid, $fieldName) {
    $nodes = [];
    $nodeIds = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('status', 1)
      ->condition($fieldName, $listNid, '=')
      ->execute();

    if (!empty($nodeIds)) {
      // Load the nodes.
      $nodes = $this->entityTypeManager
        ->getStorage('node')
        ->loadMultiple($nodeIds);
    }

    return $nodes;
  }

  /**
   * Build the list of nodes to add to the reverse entity reference.
   *
   * @param array $listNodes
   *   An array of node objects that reference the list.
   *
   * @return array
   *   An array of list item objects containing target node data.
   */
  protected function buildReverseEntityReferenceList(array $listNodes) : array {
    $list = [];
    foreach ($listNodes as $listNode) {
      $list_item = new \stdClass();
      $list_item->target_type = $listNode->getEntityTypeId();
      $list_item->target_bundle = $listNode->getType();
      $list_item->target_id = $listNode->id();
      $list_item->target_uuid = $listNode->uuid();
      // Add the reverse entitity reference to the array.
      $list[] = $list_item;
    }

    return $list;
  }

}
