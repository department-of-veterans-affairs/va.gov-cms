<?php

namespace Drupal\va_gov_dashboards\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * For processing and keeping state of a vet center dashboard.
 */
class VetCenterDashboard {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route match interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The node id of the facility that matches the section.
   *
   * @var int|null
   */
  protected $facilityNid = NULL;

  /**
   * The node id of the locations page matches the section.
   *
   * @var int|null
   */
  protected $locationNid = NULL;

  /**
   * The term id of the currently viewed section.
   *
   * @var int|null
   */
  protected $sectionTid = NULL;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Provides an interface for classes representing the result of routing.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->setSectionTid();
    $this->setFacilityNid();
    $this->setLocationNid();
  }

  /**
   * Set the term id of the currently viewed section.
   */
  public function setSectionTid() {
    if ($this->routeMatch->getRouteName() === 'entity.taxonomy_term.canonical'
        || $this->routeMatch->getRouteName() === 'layout_builder.defaults.taxonomy_term.view') {
      $this->sectionTid = $this->routeMatch->getRawParameter('taxonomy_term') ? $this->routeMatch->getRawParameter('taxonomy_term') : NULL;
    }
  }

  /**
   * Set the node id of the facility that matches the section.
   */
  public function setFacilityNid() {
    if ($this->sectionTid) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
        'type' => 'vet_center',
        'field_administration' => $this->sectionTid,
      ]);

      if (!empty($nodes)) {
        $node = reset($nodes);
        $this->facilityNid = $node->id();
      }
    }
  }

  /**
   * Set the node id of the locations page matches the section.
   */
  public function setLocationNid() {
    if ($this->sectionTid) {
      $locations_nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
        'type' => 'vet_center_locations_list',
        'field_administration' => $this->sectionTid,
      ]);
      if (!empty($locations_nodes)) {
        $locations_node = reset($locations_nodes);
        $this->locationNid = $locations_node->id();
      }
    }
  }

  /**
   * Get the node id of the facility that matches the section.
   *
   * @return int|null
   *   The nid for the matching vet center facility if one was found.
   */
  public function getFacilityNid() {
    return $this->facilityNid;
  }

  /**
   * Get the node id of the locations page matches the section.
   *
   * @return int|null
   *   The nid for the location page if one was found.
   */
  public function getLocationNid() {
    return $this->locationNid;
  }

  /**
   * Get the term id of the currently viewed section.
   *
   * @return int|null
   *   The tid for the section if one was found.
   */
  public function getSectionTid() {
    return $this->sectionTid;
  }

}
