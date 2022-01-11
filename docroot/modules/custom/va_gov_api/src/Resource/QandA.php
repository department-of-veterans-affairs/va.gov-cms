<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\va_gov_api\ResourceObjectBuilders\NodeQa;
use Symfony\Component\HttpFoundation\Request;

/**
 * Resource for collecting qa data by path.
 */
class QandA extends VaGovApiEntityResourceBase {

  /**
   * {@inheritDoc}
   */
  protected function collectResourceData(Request $request, ResourceType $resource_type) {
    // Doing this as a case/switch for now. There are almost certainly better
    // ways to do this.
    switch ($resource_type->getTypeName()) {
      case 'node--q_a':
        $this->collectQaData($request, $resource_type);
        break;
    }

    // The endpoint must vary on the item-path; therefore, it must be added as
    // a cache context.
    $item_path_context = (new CacheableMetadata())->addCacheContexts(['url.query_args:item-path']);
    $this->addCacheableDependency($item_path_context);
  }

  /**
   * Collect `qa` entities to be returned in the response.
   *
   * Given a path, retrieves the `qa` node that should show there, constructs a
   * ResponseObject for it, and adds it to cacheableDependencies.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType we want to collect data for.
   */
  private function collectQaData(Request $request, ResourceType $resource_type) {
    $path = $request->get('item-path');
    if (is_null($path)) {
      return;
    }

    $route = Url::fromUserInput($path);
    if (!$route->isRouted()) {
      // This is an error so error out.
      // @todo return actual error responses rather than null.
      return;
    }
    if (!$route->access()) {
      // @todo return actual error responses rather than null.
      return;
    }
    $params = $route->getRouteParameters();
    // Assumes a `entity key` -> 'entity id' value.
    $entity_type = key($params);
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($params[$entity_type]);
    if ($entity->bundle() === 'q_a') {
      $resource_object = NodeQa::buildResourceObject($entity, $resource_type);
      $this->addResourceObject($resource_object);
      $this->addCacheableDependency($entity);
    }
  }

}
