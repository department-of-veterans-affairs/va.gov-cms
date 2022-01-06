<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi_resources\Resource\EntityResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\jsonapi\ResourceResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Get a response for Banner resource.
 */
class VaGovApiEntityResourceBase extends EntityResourceBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * An array of ResourceObjects to use to construct the response.
   *
   * @var \Drupal\jsonapi\JsonApiResource\ResourceObject[]
   */
  private $resourceObjects = [];

  /**
   * An array of CacheableDependency objects to use to construct the response.
   *
   * @var \Drupal\Core\Cache\CacheableDependencyInterface[]
   */
  private $cacheableDependencies = [];

  /**
   * Constructs a new EntityResourceBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Tne entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger->get('va_gov_api');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
    );
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
   * Construct the ResourceResponse.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   The response.
   */
  protected function constructJsonapiResponse(Request $request) {
    $resource_object_data = new ResourceObjectData($this->resourceObjects);
    /** @var \Drupal\jsonapi\ResourceResponse $response */
    $response = $this->createJsonapiResponse($resource_object_data, $request);

    // Add any entities to the response cacheable dependencies.
    foreach ($this->cacheableDependencies as $cacheable_dependency) {
      $response->addCacheableDependency($cacheable_dependency);
    }
    return $response;
  }

  /**
   * Process the resource request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\jsonapi\ResourceType\ResourceType[] $resource_types
   *   The route resource types.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   The response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function process(Request $request, array $resource_types) : ResourceResponse {
    foreach ($resource_types as $resource_type) {
      $this->collectResourceData($request, $resource_type);
    }
    $response = $this->constructJsonapiResponse($request);

    return $response;
  }

  /**
   * For a given ResourceType, collect its resource data.
   *
   * Each ResourceType may have its own logic for retrieval, and will have its
   * own logic for its ResourceObject.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType we want to collect data for.
   */
  protected function collectResourceData(Request $request, ResourceType $resource_type) {
    // To be implemented by Resource-specific classes.
    $this->logger->warning('Extending classes must implement method collectResourceData for themselves.');
  }

}
