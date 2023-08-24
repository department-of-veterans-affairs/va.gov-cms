<?php

namespace Drupal\va_gov_api\Resources;

use Drupal\jsonapi\ResourceResponse;
use Drupal\jsonapi_resources\Resource\EntityResourceBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi\ResourceType\ResourceType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The BannerAlerts JSON:API resource.
 */
class BannerAlerts extends EntityResourceBase implements ContainerInjectionInterface {

  /**
   * An array of CacheableDependency objects to use to construct the response.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface[]
   */
  protected $cacheableDependencies = [];

  /**
   * An array of ResourceObjects to use to construct the response.
   *
   * @var \Drupal\jsonapi\JsonApiResource\ResourceObject[]
   */
  protected $resourceObjects = [];

  /**
   * The path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The page cache kill switch service.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $pageCacheKillSwitch;

  /**
   * The BannerAlerts resource constructor.
   *
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $page_cache_kill_switch
   *   The page cache kill switch.
   */
  public function __construct(PathMatcherInterface $path_matcher, PathValidatorInterface $path_validator, KillSwitch $page_cache_kill_switch) {
    $this->pathMatcher = $path_matcher;
    $this->pathValidator = $path_validator;
    $this->pageCacheKillSwitch = $page_cache_kill_switch;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.matcher'),
      $container->get('path.validator'),
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * Process the resource request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\jsonapi\ResourceType\ResourceType[] $resource_types
   *   The route resource types.
   * @param mixed $resource_tag
   *   An arg that is passed.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   The response.
   */
  public function process(Request $request, array $resource_types, $resource_tag = NULL): ResourceResponse {
    // Vary the response by item-path.
    $this->addItemPathCacheableDependency();

    // Make the resource parameter available to other methods.
    if (!is_null($resource_tag)) {
      // @phpstan-ignore-next-line
      $this->addRouteParameter('resource_tag', $resource_tag);
    }

    $path = $request->get('item-path');

    // Collect the data.
    if (!is_null($path)) {
      foreach ($resource_types as $resource_type) {
        switch ($resource_type->getTypeName()) {
          case 'node--banner':
            $this->collectBannerData($path, $resource_type);
            break;

          case 'node--promo_banner':
            $this->collectPromoBannerData($path, $resource_type);
            break;

          case 'node--full_width_banner_alert':
            $this->collectFullWidthBannerAlertData($path, $resource_type);
            break;
        }
      }
    }

    return $this->buildResponse($request);
  }

  /**
   * Build a resource response.
   */
  protected function buildResponse($request) {
    $resource_object_data = new ResourceObjectData($this->resourceObjects);

    /** @var \Drupal\Core\Cache\CacheableResponseInterface $response */
    $response = $this->createJsonapiResponse($resource_object_data, $request);

    foreach ($this->cacheableDependencies as $cacheable_dependency) {
      $response->addCacheableDependency($cacheable_dependency);
    }

    // If it's an empty response, then we shouldn't cache it at all -- we don't
    // have a good way of invalidating the cache of an empty result (since the
    // cache invlidation is based on the nodes that are actually included in the
    // response).
    if (empty($this->resourceObjects) || empty($this->cacheableDependencies)) {
      $this->pageCacheKillSwitch->trigger();
    }

    return $response;
  }

  /**
   * Collect `banner` entities to be returned in the response.
   *
   * Given a path, retrieves any `banner` that should show there, constructs a
   * ResponseObject for it, and adds it to cacheableDependencies.
   *
   * @param string $path
   *   The path to the item to find banners for.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType we want to collect data for.
   */
  protected function collectBannerData(string $path, ResourceType $resource_type) {
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Get all published banner nodes.
    $banner_nids = $node_storage->getQuery()
      ->condition('type', 'banner')
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    /** @var \Drupal\node\NodeInterface[] $banners */
    $banners = $node_storage->loadMultiple(array_values($banner_nids) ?? []);

    // Filter the banner list to just the ones that should be displayed for the
    // provided item path.
    $banners = array_filter($banners, function ($item) use ($path) {
      // PathMatcher expects a newline delimited string for multiple paths.
      $patterns = '';
      foreach ($item->field_target_paths->getValue() as $target_path) {
        $patterns .= $target_path['value'] . "\n";
      }

      return $this->pathMatcher->matchPath($path, $patterns);
    });

    // Add the banners to the response.
    foreach ($banners as $entity) {
      $this->addEntityToResponse($resource_type, $entity);
    }
  }

  /**
   * Collect `promo_banner` entities to be returned in the response.
   *
   * Given a path, retrieves any `promo_banner` that should show there,
   *  constructs a ResponseObject for it, and adds it to cacheableDependencies.
   *
   * @param string $path
   *   The path to the item to find promo_banners for.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType we want to collect data for.
   */
  protected function collectPromoBannerData(string $path, ResourceType $resource_type) {
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Get all published promo_banner nodes.
    $promo_banner_nids = $node_storage->getQuery()
      ->condition('type', 'promo_banner')
      ->condition('status', TRUE)
      ->accessCheck(FALSE)
      ->execute();
    /** @var \Drupal\node\NodeInterface[] $promo_banners */
    $promo_banners = $node_storage->loadMultiple(array_values($promo_banner_nids) ?? []);

    // Filter the promo_banner list to just the ones that should be displayed
    // for the provided item path.
    $promo_banners = array_filter($promo_banners, function ($item) use ($path) {
      // PathMatcher expects a newline delimited string for multiple paths.
      $patterns = '';
      foreach ($item->field_target_paths->getValue() as $target_path) {
        $patterns .= $target_path['value'] . "\n";
      }

      return $this->pathMatcher->matchPath($path, $patterns);
    });

    // Add the promo_banners to the response.
    foreach ($promo_banners as $entity) {
      $this->addEntityToResponse($resource_type, $entity);
    }
  }

  /**
   * Collect `full_width_banner_alert` entities to be returned in the response.
   *
   * Given a path, retrieves any `full_width_banner_alert` that should show
   * there, constructs a ResponseObject for it, and adds it to
   * cacheableDependencies.
   *
   * @param string $path
   *   The path to the item to find full_width_banner_alerts for.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType we want to collect data for.
   */
  protected function collectFullWidthBannerAlertData(string $path, ResourceType $resource_type) {
    // Find the first fragment of the path; this will correspond to a facility,
    // if this is a facility page of some kind.
    $region_fragment = '__not_a_real_url';
    $path_pieces = explode("/", $path);
    if (count($path_pieces) > 1) {
      $region_fragment = "/" . $path_pieces[1];
    }

    // Resolve the region fragment to a URL object.
    $url = $this->pathValidator->getUrlIfValidWithoutAccessCheck($region_fragment);
    if ($url === FALSE || !$url->isRouted() || !isset($url->getRouteParameters()['node'])) {
      // If the alias is invalid, it's not a routed URL, or there is not a node
      // in the route params, there's not much else that can be done here.
      return;
    }

    // Load the system that we found.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $system_nid = $url->getRouteParameters()['node'];
    /** @var \Drupal\node\NodeInterface $system_node */
    $system_node = $node_storage->load($system_nid);

    // If it's not a published VAMC system node, bail early.
    if (is_null($system_node) || $system_node->getType() != 'health_care_region_page' || $system_node->isPublished() === FALSE) {
      return;
    }

    // Find all operating status nodes which have this system as their office.
    $operating_status_nids = $node_storage->getQuery()
      ->condition('type', 'vamc_operating_status_and_alerts')
      ->condition('status', TRUE)
      ->condition('field_office', $system_node->id())
      ->accessCheck(FALSE)
      ->execute();

    // If there are no operating status nids, bail.
    if (count($operating_status_nids) === 0) {
      return;
    }

    // Find any facility banners connected to the operating status nodes.
    $facility_banner_nids = $node_storage->getQuery()
      ->condition('type', 'full_width_banner_alert')
      ->condition('status', TRUE)
      ->condition('field_banner_alert_vamcs', array_values($operating_status_nids), 'IN')
      ->accessCheck(FALSE)
      ->execute();

    /** @var \Drupal\node\NodeInterface[] $facility_banners */
    $facility_banners = $node_storage->loadMultiple($facility_banner_nids);

    // Add the banners to the response.
    foreach ($facility_banners as $entity) {
      $this->addEntityToResponse($resource_type, $entity);
    }
  }

  /**
   * Add a cacheable dependency and resource object for an entity.
   *
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The resource type of the entity.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object to add to the response.
   */
  protected function addEntityToResponse(ResourceType $resource_type, EntityInterface $entity) {
    $this->addCacheableDependency($entity);
    $this->addResourceObject(ResourceObject::createFromEntity($resource_type, $entity));
  }

  /**
   * The endpoint cache must vary on the item-path.
   */
  protected function addItemPathCacheableDependency() {
    $item_path_context = (new CacheableMetadata())->addCacheContexts(['url.query_args:item-path']);
    $this->addCacheableDependency($item_path_context);
  }

  /**
   * Add a CacheableDependency object to be used in constructing our response.
   *
   * @param mixed $cacheable_dependency
   *   The dependency object to add to our response.
   */
  protected function addCacheableDependency($cacheable_dependency) {
    if (!($cacheable_dependency instanceof CacheableMetadata)) {
      $cacheable_dependency = CacheableMetadata::createFromObject($cacheable_dependency);
    }
    $this->cacheableDependencies[] = $cacheable_dependency;
  }

  /**
   * Add a response object to be used in constructing our response.
   *
   * @param \Drupal\jsonapi\JsonApiResource\ResourceObject $resource_object
   *   The ResourceObject to add to our response.
   */
  protected function addResourceObject(ResourceObject $resource_object) {
    $this->resourceObjects[] = $resource_object;
  }

}
