<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Resource for collecting banner data by path.
 */
class BannerAlerts extends VaGovApiEntityResourceBase {

  /**
   * {@inheritDoc}
   */
  protected function collectResourceData(Request $request, ResourceType $resource_type) {
    // Doing this as a case/switch for now. There are almost certainly better
    // ways to do this.
    switch ($resource_type->getTypeName()) {
      case 'node--banner':
        $this->collectBannerData($request, $resource_type);
        break;

      case 'node--full_width_banner_alert':
        $this->collectFullWidthBannerAlertData($request, $resource_type);
        break;
    }

    // The endpoint must vary on the item-path; therefore, it must be added as
    // a cache context.
    $item_path_context = (new CacheableMetadata())->addCacheContexts(['url.query_args:item-path']);
    $this->addCacheableDependency($item_path_context);
  }

  /**
   * Collect `banner` entities to be returned in the response.
   *
   * Given a path, retrieves any `banner` that should show there, constructs a
   * ResponseObject for it, and adds it to cacheableDependencies.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType we want to collect data for.
   */
  private function collectBannerData(Request $request, ResourceType $resource_type) {
    $path = $request->get('item-path');
    if (is_null($path)) {
      return;
    }

    // The business logic for displaying a full width alert is: each full width
    // alert has a paths field which indicates where it should or should not be
    // displayed. We need to load all `banner` entities in order to do this
    // check.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery();
    // The machine name for this entity is `banner`.
    $query->condition('type', 'banner')->condition('status', TRUE);
    $banner_nids = $query->execute();
    /** @var \Drupal\node\NodeInterface[] $banners */
    $banners = [];
    if (count($banner_nids)) {
      $banners = $node_storage->loadMultiple(array_values($banner_nids));
    }

    // @todo should this be injected? phpstan thinks so.
    // @phpstan-ignore-next-line
    $path_matcher = \Drupal::service('path.matcher');
    // Check each banner to see if it should display, using Drupal's built-in
    // PathMatcher class.
    /** @var \Drupal\node\NodeInterface[] $banners */
    foreach ($banners as $idx => $banner) {
      $patterns = '';
      // Convert values to a string that PathMatcher expects. field_target_paths
      // is a multiple-value single line text field, while PathMatcher expects
      // a single multiline value.
      $pathChecks = $banner->field_target_paths->getValue();
      foreach ($pathChecks as $pathCheck) {
        $patterns = $patterns . $pathCheck['value'] . "\n";
      }
      if (!$path_matcher->matchPath($path, $patterns)) {
        unset($banners[$idx]);
      }
    }

    // Construct and add a ResourceObject for each matching entity found, and
    // add the entity as a cacheableDependency.
    foreach ($banners as $entity) {
      $resource_object = $this->createBannerResourceObject($entity, $resource_type);
      $this->addResourceObject($resource_object);
      $this->addCacheableDependency($entity);
    }
  }

  /**
   * Create a ResourceObject from a `banner` entity.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The `banner` entity.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType for `banner` entities.
   *
   * @return \Drupal\jsonapi\JsonApiResource\ResourceObject
   *   A ResourceObject constructed from a `banner` entity.
   */
  private function createBannerResourceObject(NodeInterface $entity, ResourceType $resource_type) {
    /** @var \Drupal\taxonomy\TermInterface $section_term */
    $section_term = $entity->field_administration->entity;
    $data = [
      'nid' => $entity->id(),
      'uuid' => $entity->uuid(),
      'langcode' => $entity->language()->getId(),
      'status' => $entity->isPublished(),
      'bundle' => $entity->getType(),
      'heading' => $entity->label(),
      'moderation_state' => $entity->get('moderation_state')->getString(),
      'alert_type' => $entity->field_alert_type->value,
      // @phpstan-ignore-next-line
      'text' => $entity->body->processed,
      'dismissible' => $entity->field_dismissible_option->value,
      'section_name' => $section_term->label(),
      'paths' => array_column($entity->field_target_paths->getValue(), 'value'),
    ];

    return new ResourceObject(
      $entity,
      $resource_type,
      $entity->uuid(),
      NULL,
      $data,
      new LinkCollection([])
    );
  }

  /**
   * Collect `full_width_banner_alert` entities to be returned in the response.
   *
   * Given a path, retrieves any `full_width_banner_alert` that should show
   * there, constructs a ResponseObject for it, and adds it to
   * cacheableDependencies.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType we want to collect data for.
   */
  private function collectFullWidthBannerAlertData(Request $request, ResourceType $resource_type) {
    $path = $request->get('item-path');
    if (is_null($path)) {
      return;
    }

    // Find the first fragment of the path; this will correspond to a facility,
    // if this is a facility page of some kind.
    $path_pieces = explode("/", $path);
    if (count($path_pieces) > 1) {
      $region_fragment = "/" . $path_pieces[1];
    }
    else {
      // We don't have an identifiable url fragment, so exit.
      return;
    }

    // The business logic for displaying a facility alert is, take the initial
    // url fragment of a url, which in the case of facilities corresponds to a
    // facility system entity. Check whether that facility system entity is
    // connected to a facility alert through the alert's operating status and
    // alert reference. The current author is aware of the state of this logic.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $system_query = $node_storage->getQuery()
      ->condition('type', 'health_care_region_page')->condition('status', TRUE);
    $system_nodes = $node_storage->loadMultiple($system_query->execute());
    foreach ($system_nodes as $system_node) {
      // Try to find a system_node that matches our current region_fragment.
      // The following url dance is here to avoid early rendering.
      $url = $system_node->toUrl()->toString(TRUE);
      $url_string = $url->getGeneratedUrl();
      if ($region_fragment === $url_string) {
        // Find all operating status nodes which have this system node as their
        // office.
        $operating_status_query = $node_storage->getQuery()
          ->condition('type', 'vamc_operating_status_and_alerts')
          ->condition('status', TRUE)
          ->condition('field_office', $system_node->id());
        $operating_status_node_ids = array_values($operating_status_query->execute());
        // Find any facility banners which have these operating status nodes
        // connected to them.
        $facility_banner_query = $node_storage->getQuery()
          ->condition('type', 'full_width_banner_alert')
          ->condition('status', TRUE)
          ->condition('field_banner_alert_vamcs', $operating_status_node_ids, 'IN');
        /** @var \Drupal\node\NodeInterface[] $facility_banners */
        $facility_banners = $node_storage->loadMultiple($facility_banner_query->execute());

        // Construct and add a ResourceObject for each matching entity found,
        // and add the entity as a cacheableDependency.
        foreach ($facility_banners as $entity) {
          $resource_object = $this->createFullWidthBannerAlertResourceObject($entity, $resource_type);
          $this->addResourceObject($resource_object);
          $this->addCacheableDependency($entity);
        }
        // Facility url fragments are unique, so if we have matched, we can stop
        // checking against the rest of the facility system pages.
        break;
      }
    }
  }

  /**
   * Create a ResourceObject from a `full_width_banner_alert` entity.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The `full_width_banner_alert` entity.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType for `full_width_banner_alert` entities.
   *
   * @return \Drupal\jsonapi\JsonApiResource\ResourceObject
   *   A ResourceObject constructed from a `full_width_banner_alert` entity.
   */
  private function createFullWidthBannerAlertResourceObject(NodeInterface $entity, ResourceType $resource_type) {
    /** @var \Drupal\taxonomy\TermInterface $section_term */
    $section_term = $entity->field_administration->entity;
    $data = [
      'nid' => $entity->id(),
      'uuid' => $entity->uuid(),
      'langcode' => $entity->language()->getId(),
      'status' => $entity->isPublished(),
      'bundle' => $entity->getType(),
      'heading' => $entity->label(),
      'moderation_state' => $entity->get('moderation_state')->getString(),
      // @phpstan-ignore-next-line
      'text' => $entity->field_body->processed,
      'alert_type' => $entity->field_alert_type->value,
      'dismissable' => $entity->field_alert_dismissable->value,
      'section_name' => $section_term->label(),
      // Paths is not relevant to the facility banner, but it's in spec.
      'paths' => '',
      // @phpstan-ignore-next-line
      'situational_info' => $entity->field_banner_alert_situationinfo->processed,
      'show_find_facilities_cta' => $entity->field_alert_find_facilities_cta->value,
      'show_operating_status_cta' => $entity->field_alert_operating_status_cta->value,
      'show_email_update_button' => $entity->field_alert_email_updates_button->value,
      'limit_subpages' => $entity->field_alert_inheritance_subpages->value,
      'send_email_updates' => $entity->field_operating_status_sendemail->value,
      'situation_updates' => 'String for POC; this should point at a schema rather than being a string.',
    ];
    return new ResourceObject(
      $entity,
      $resource_type,
      $entity->uuid(),
      NULL,
      $data,
      new LinkCollection([])
    );
  }

}
