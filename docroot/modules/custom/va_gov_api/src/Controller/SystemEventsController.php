<?php

namespace Drupal\va_gov_api\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Returns responses for the System Events API.
 *
 * Returns upcoming events for a VAMC system, handling recurring events
 * and Lovell variant pages.
 */
class SystemEventsController extends ControllerBase {

  /**
   * Lovell administration entity IDs.
   */
  const LOVELL_FEDERAL_ADMIN_ID = "347";
  const LOVELL_TRICARE_ADMIN_ID = "1039";
  const LOVELL_VA_ADMIN_ID = "1040";

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(
    SerializerInterface $serializer,
    EntityTypeManagerInterface $entity_type_manager,
  ) {
    $this->serializer = $serializer;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('serializer'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Get system events by system ID.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The system events.
   */
  public function systemEvents(Request $request) {
    $system_id = $request->get('system_id');
    if (!$system_id) {
      return new JsonResponse(['error' => 'No system_id provided.'], 400);
    }

    // Load the VAMC system node.
    $node_storage = $this->entityTypeManager->getStorage('node');
    /** @var \Drupal\node\NodeInterface|null $system_node */
    $system_node = $node_storage->load($system_id);

    // Validate system node.
    if (!$system_node || $system_node->getType() !== 'health_care_region_page' || !$system_node->isPublished()) {
      return new JsonResponse(['error' => 'Invalid system_id or system not found.'], 404);
    }

    // Determine if this is a Lovell variant system.
    $is_lovell_variant = $this->isLovellVariantSystem($system_node);

    // Fetch events.
    $events = $this->fetchSystemEvents($system_id, $is_lovell_variant);

    // Build response.
    $response = new CacheableJsonResponse([
      'is_lovell_variant' => $is_lovell_variant,
      'data' => $events,
    ]);

    // Add cache metadata.
    $response->getCacheableMetadata()->addCacheContexts(['url.query_args:system_id']);
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
  protected function fetchSystemEvents(string $system_id, bool $is_lovell_variant): array {
    $current_timestamp = time();

    // Try to get featured events first.
    $featured_events = $this->fetchEvents($system_id, TRUE, $is_lovell_variant, $current_timestamp);

    if (!empty($featured_events)) {
      // Sort by first future occurrence and limit to 2.
      $sorted = $this->sortEventsByNextOccurrence($featured_events, $current_timestamp);
      return array_slice($sorted, 0, 2);
    }

    // If no featured events, get one non-featured event.
    $non_featured_events = $this->fetchEvents($system_id, FALSE, $is_lovell_variant, $current_timestamp);
    if (!empty($non_featured_events)) {
      $sorted = $this->sortEventsByNextOccurrence($non_featured_events, $current_timestamp);
      return array_slice($sorted, 0, 1);
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

    // Query for events with this system as the office.
    // Note: We can't reliably query Smart Date multi-value fields for
    // recurring events at the database level, so we'll query by other criteria
    // and filter by date in PHP.
    $query = $node_storage->getQuery()
      ->condition('type', 'event')
      ->condition('status', 1)
      ->condition('field_featured', $featured ? 1 : 0)
      ->accessCheck(FALSE);

    // Query events where field_listing.field_office = system_id.
    // We need to query through the listing node to get the office.
    // First, get all event listing nodes that have this system as their office.
    $listing_storage = $this->entityTypeManager->getStorage('node');
    $listing_query = $listing_storage->getQuery()
      ->condition('type', 'event_listing')
      ->condition('status', 1)
      ->condition('field_office.target_id', $system_id)
      ->accessCheck(FALSE);
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
        ->accessCheck(FALSE);
      $lovell_federal_office_ids = $office_query->execute();

      // Then, query listings that have these offices.
      if (!empty($lovell_federal_office_ids)) {
        $lovell_federal_listing_query = $listing_storage->getQuery()
          ->condition('type', 'event_listing')
          ->condition('status', 1)
          ->condition('field_office.target_id', array_values($lovell_federal_office_ids), 'IN')
          ->accessCheck(FALSE);
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

    // Filter events by listing IDs.
    $query->condition('field_listing.target_id', $all_listing_ids, 'IN');
    $event_ids = $query->execute();

    if (empty($event_ids)) {
      return [];
    }

    // Load the event nodes.
    /** @var \Drupal\node\NodeInterface[] $events */
    $events = $node_storage->loadMultiple(array_values($event_ids));

    // Filter events to only those with at least one future occurrence.
    // Smart Date stores recurring events as multiple field deltas, each with a
    // 'value' property (start timestamp). We need to check all deltas, not
    // just one.
    $events_with_future_occurrences = [];
    foreach ($events as $event) {
      $date_ranges = $event->get('field_datetime_range_timezone')->getValue();
      if (empty($date_ranges)) {
        continue;
      }

      // Check if any occurrence has a value >= current_timestamp.
      foreach ($date_ranges as $occurrence) {
        if (isset($occurrence['value']) && $occurrence['value'] >= $current_timestamp) {
          // This event has at least one future occurrence.
          $events_with_future_occurrences[] = $event;
          break;
        }
      }
    }

    return $events_with_future_occurrences;
  }

  /**
   * Sort events by their first future occurrence.
   *
   * @param \Drupal\node\NodeInterface[] $events
   *   Array of event nodes.
   * @param int $current_timestamp
   *   Current Unix timestamp.
   *
   * @return array
   *   Array of event data with first future occurrence.
   */
  protected function sortEventsByNextOccurrence(array $events, int $current_timestamp): array {
    $events_with_occurrences = [];

    foreach ($events as $event) {
      $first_occurrence = $this->getFirstFutureOccurrence($event, $current_timestamp);
      if ($first_occurrence) {
        $events_with_occurrences[] = [
          'event' => $event,
          'next_timestamp' => $first_occurrence['value'],
        ];
      }
    }

    // Sort by next occurrence timestamp.
    usort($events_with_occurrences, function ($a, $b) {
      return $a['next_timestamp'] <=> $b['next_timestamp'];
    });

    // Return normalized event data.
    $result = [];
    foreach ($events_with_occurrences as $item) {
      $normalized = $this->serializer->normalize($item['event']);
      // Filter field_datetime_range_timezone to only future occurrences.
      if (isset($normalized['field_datetime_range_timezone'])) {
        $normalized['field_datetime_range_timezone'] = array_filter(
          $normalized['field_datetime_range_timezone'],
          function ($occurrence) use ($current_timestamp) {
            return isset($occurrence['value']) && $occurrence['value'] >= $current_timestamp;
          }
        );
        // Re-index array.
        $normalized['field_datetime_range_timezone'] = array_values($normalized['field_datetime_range_timezone']);
      }
      $result[] = $normalized;
    }

    return $result;
  }

  /**
   * Get the first future occurrence from an event.
   *
   * @param \Drupal\node\NodeInterface $event
   *   The event node.
   * @param int $current_timestamp
   *   Current Unix timestamp.
   *
   * @return array|null
   *   The first future occurrence data or null if none found.
   */
  protected function getFirstFutureOccurrence($event, int $current_timestamp): ?array {
    $date_ranges = $event->get('field_datetime_range_timezone')->getValue();
    if (empty($date_ranges)) {
      return NULL;
    }

    // Find first occurrence with value >= current_timestamp.
    foreach ($date_ranges as $occurrence) {
      if (isset($occurrence['value']) && $occurrence['value'] >= $current_timestamp) {
        return $occurrence;
      }
    }

    return NULL;
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
