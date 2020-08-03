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
   * @var string[]
   */
  protected static $listEntityTypes = [
    'event_listing',
    'health_services_listing',
    'leadership_listing',
    'locations_listing',
    'press_releases_listing',
    'publication_listing',
    'story_listing',
  ];

  /**
   * An array of field names to treat as a reference to a list.
   *
   * @var array
   */
  protected static $listingFields = [
    'field_listing',
  ];

  /**
   * ListDataCompiler constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Update related lists.
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
  public function updateLists(ContentEntityInterface $entity, TomeExporter $tomeExporter) {
    $listNids = $this->getListNids($entity);
    $listNidsToUpdateLater = [];
    $listEntitiesToUpdateLater = [];
    if (!empty($listNids)) {
      // Process each list this entity should appear in.
      foreach ($listNids as $listNid => $listFieldName) {
        $listNidsToUpdateLater[] = $this->updateList($entity, $listNid, $listFieldName);
      }
      $listNidsToUpdateLater = array_filter($listNidsToUpdateLater);
    }
    if (!empty($listNidsToUpdateLater)) {
      // @todo Add a check to bypass this if the bulk export is running.
      // https://github.com/ ... /va.gov-cms/issues/2481.
      // Load the list entities that need to be exported.
      $listEntitiesToUpdateLater = $this->entityTypeManager->getStorage('node')
        ->loadMultiple($listNidsToUpdateLater);
      // Export the lists to have their reverse field list updated.
      foreach ($listEntitiesToUpdateLater as $listEntityToUpdate) {
        // Update the list export json.
        $tomeExporter->exportContent($listEntityToUpdate);
      }
    }
  }

  /**
   * Update a list to add the list data as ->reverse_field_list.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity is a list or in a list.
   * @param int $listNid
   *   The node id of ths list this entity belongs in.
   * @param string $listFieldName
   *   The field machine name to use as the reverse entity reference.
   *
   * @return int
   *   The node id of a list to be updated.
   */
  public function updateList(ContentEntityInterface $entity, $listNid, $listFieldName) {
    // Check if $entity is the list, or if we need to get the list.
    $current_nid = $entity->id();
    if ($this->isList($entity) && ((int) $entity->id() === $listNid)) {
      // Add the list to the list entity.
      $listNodes = $this->queryListItemNodes($listNid, $listFieldName);
      $list = $this->buildList($listNodes);
      $entity->values['reverse_field_list'] = $list;
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
      if (in_array($entity->bundle(), static::$listEntityTypes)) {
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
      $listNids[$entity->id()] = 'field_listing';
    }
    else {
      // Look to see if it has any listing fields.
      foreach (static::$listingFields as $listingField) {
        if ($entity->hasField($listingField)) {
          // It has a field that is a reference to a list.
          $lists = $entity->$listingField;
          foreach ($lists as $list) {
            $listNids[$list->target_id] = $listingField;
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
   * Build the list of nodes to add to the list.
   *
   * @param array $listNodes
   *   An array of node objects that reference the list.
   *
   * @return array
   *   An array of list item objects containing target node data.
   */
  protected function buildList(array $listNodes) : array {
    $list = [];
    foreach ($listNodes as $listNode) {
      $list_item = new \stdClass();
      $list_item->target_type = $listNode->getEntityTypeId();
      $list_item->target_bundle = $listNode->getType();
      $list_item->target_id = $listNode->id();
      $list_item->target_uuid = $listNode->uuid();

      $list[] = $list_item;
    }

    return $list;
  }

}
