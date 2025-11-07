<?php

namespace Drupal\va_gov_api\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for the System Events API.
 *
 * Returns upcoming events for a VAMC system, handling recurring events
 * and Lovell variant pages.
 */
class VamcSystemEventsController extends ControllerBase {

  /**
   * Lovell administration entity IDs.
   */
  const LOVELL_FEDERAL_ADMIN_ID = "347";
  const LOVELL_TRICARE_ADMIN_ID = "1039";
  const LOVELL_VA_ADMIN_ID = "1040";

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    Connection $database,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database')
    );
  }

  /**
   * Get featured events by vamc-system(health_care_region_page) NID.
   *
   * @param int $nid
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The system events.
   */
  public function getFeaturedEvents($nid) {
    if (!$nid) {
      return new JsonResponse(['error' => 'No nid provided.'], 400);
    }

    // Load the VAMC system node.
    $node_storage = $this->entityTypeManager->getStorage('node');
    /** @var \Drupal\node\NodeInterface|null $system_node */
    $system_node = $node_storage->load($nid);

    // Validate system node.
    if (!$system_node || $system_node->getType() !== 'health_care_region_page' || !$system_node->isPublished()) {
      return new JsonResponse(['error' => 'Invalid ID or vamc-system not found.'], 404);
    }

    // Determine if this is a Lovell variant system.
    $is_lovell_variant = $this->isLovellVariantSystem($system_node);

    // Fetch events.
    $events = $this->fetchFeaturedEvents($nid, $is_lovell_variant);

    // Build response.
    $response = new CacheableJsonResponse([
      'data' => $events,
    ]);

    // Add cache metadata.
    $response->getCacheableMetadata()->addCacheTags(['node_list:event']);
    if ($system_node) {
      $response->getCacheableMetadata()->addCacheTags($system_node->getCacheTags());
    }
    // Cache for 1 hour.
    // $response->getCacheableMetadata()->setCacheMaxAge(3600);
    $response->getCacheableMetadata()->setCacheMaxAge(0);

    return $response;
  }

  /**
   * Fetch events for a system, handling Lovell variants.
   *
   * @param string $system_id
   *   The system node ID.
   * @param bool $is_lovell_variant
   *   Whether this is a Lovell variant system.
   *
   * @return array
   *   Array of event data, limited to 2 featured events or 1 fallback.
   */
  protected function fetchFeaturedEvents(string $system_id, bool $is_lovell_variant): array {
    $current_timestamp = time();

    // Try to get featured events first.
    $featured_events = $this->fetchEvents($system_id, TRUE, $is_lovell_variant, $current_timestamp);

    if (!empty($featured_events)) {
      return $featured_events;
    }

    $non_featured_events = $this->fetchEvents($system_id, FALSE, $is_lovell_variant, $current_timestamp);
    if (!empty($non_featured_events)) {
      return $non_featured_events;
    }

    return [];
  }

  /**
   * Fetch events for a system.
   *
   * @param string $system_id
   *   The system node ID.
   * @param bool $featured
   *   Whether to fetch featured events.
   * @param bool $is_lovell_variant
   *   Whether this is a Lovell variant system.
   * @param int $current_timestamp
   *   Current Unix timestamp.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of event nodes.
   */
  protected function fetchEvents(string $system_id, bool $featured, bool $is_lovell_variant, int $current_timestamp): array {
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Query events where field_listing.field_office = system_id.
    // We need to query through the listing node to get the office.
    // First, get all event listing nodes that have this system as their office.
    $listing_storage = $this->entityTypeManager->getStorage('node');
    $listing_query = $listing_storage->getQuery()
      ->condition('type', 'event_listing')
      ->condition('status', 1)
      ->condition('field_office.target_id', $system_id)
      ->accessCheck(TRUE);
    $listing_ids = $listing_query->execute();

    // If this is a Lovell variant, also query events from Lovell Federal.
    // First, query offices that have Lovell Federal administration.
    $lovell_federal_listing_ids = [];
    if ($is_lovell_variant) {
      $office_storage = $this->entityTypeManager->getStorage('node');
      $office_query = $office_storage->getQuery()
        ->condition('type', 'office')
        ->condition('status', 1)
        ->condition('field_administration.target_id', self::LOVELL_FEDERAL_ADMIN_ID)
        ->accessCheck(TRUE);
      $lovell_federal_office_ids = $office_query->execute();

      // Then, query listings that have these offices.
      if (!empty($lovell_federal_office_ids)) {
        $lovell_federal_listing_query = $listing_storage->getQuery()
          ->condition('type', 'event_listing')
          ->condition('status', 1)
          ->condition('field_office.target_id', array_values($lovell_federal_office_ids), 'IN')
          ->accessCheck(TRUE);
        $lovell_federal_listing_ids = $lovell_federal_listing_query->execute();
      }
    }

    // Combine listing IDs from both sources.
    $all_listing_ids = array_merge(
      !empty($listing_ids) ? array_values($listing_ids) : [],
      !empty($lovell_federal_listing_ids) ? array_values($lovell_federal_listing_ids) : []
    );

    // If no listings found at all, return empty.
    if (empty($all_listing_ids)) {
      return [];
    }

    // Build SQL query using Drupal's database API.
    $database = $this->database;
    $current_time = strtotime('today midnight');
    $featured_value = $featured ? "1" : "0";

    // Prepare the base query.
    $query = $database->select('node_field_data', 'n')
      ->fields('n', ['nid'])
      ->fields('fd', ['field_datetime_range_timezone_value'])
      ->condition('n.type', 'event')
      ->condition('n.status', 1)
      ->range(0, $featured ? 2 : 1);

    // Join field_featured.
    $query->join('node__field_featured', 'ff', 'ff.entity_id = n.nid');
    $query->condition('ff.field_featured_value', $featured_value);

    // Join field_listing table and filter by listing IDs.
    $query->join('node__field_listing', 'fl', 'fl.entity_id = n.nid');
    $query->condition('fl.field_listing_target_id', $all_listing_ids, 'IN');

    // Join field_datetime_range_timezone.
    $query->join('node__field_datetime_range_timezone', 'fd', 'fd.entity_id = n.nid');
    $query->condition('fd.field_datetime_range_timezone_value', $current_time, '>=');
    $query->orderBy('fd.field_datetime_range_timezone_value', 'ASC');

    // Execute the query.
    $event_ids = $query->execute()->fetchAllAssoc("nid");

    if (empty($event_ids)) {
      return [];
    }

    // Load the event nodes.
    /** @var \Drupal\node\NodeInterface[] $events */
    $events = $node_storage->loadMultiple(array_keys($event_ids));
    $result = [];
    foreach ($events as $item) {
      $location_nid = $item->get("field_facility_location")->getString();
      $facility_location = NULL;
      if ($location_nid) {
        /** @var \Drupal\node\NodeInterface $facility_node */
        $facility_node = $node_storage->load($location_nid);
        $facility_location = [
          'title' => $facility_node->get('title')->value,
          'entityUrl' => $facility_node->toUrl()->toString(),
        ];
      }
      array_push($result, [
        'title' => $item->get('title')->value,
        'entityUrl' => $item->toUrl()->toString(),
        'fieldDescription' => $item->get('field_description')->value,
        'fieldDatetimeRangeTimezone' => $event_ids[$item->get('nid')->value]->field_datetime_range_timezone_value,
        'fieldFacilityLocation' => $facility_location,
        'fieldLocationHumanreadable' => $item->get("field_location_humanreadable")->value,
        'fieldFeatured' => $item->get("field_featured")->value,
      ]);
    }

    return $result;
  }

  /**
   * Check if a system is a Lovell variant.
   *
   * @param \Drupal\node\NodeInterface $system_node
   *   The system node.
   *
   * @return bool
   *   TRUE if this is a Lovell TRICARE or VA variant.
   */
  protected function isLovellVariantSystem($system_node): bool {
    if (!$system_node->hasField('field_administration')) {
      return FALSE;
    }

    $administration = $system_node->get('field_administration')->entity;
    if (!$administration) {
      return FALSE;
    }

    $admin_id = $administration->id();
    return $admin_id === self::LOVELL_TRICARE_ADMIN_ID || $admin_id === self::LOVELL_VA_ADMIN_ID;
  }

}
