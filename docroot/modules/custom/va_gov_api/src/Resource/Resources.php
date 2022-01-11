<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\va_gov_api\ResourceObjectBuilders\NodeQa;
use Symfony\Component\HttpFoundation\Request;

/**
 * Resource for returning a collection of 'resource' product nodes.
 *
 * Apologies for the naming collision. Currently limited to q_a nodes.
 */
class Resources extends VaGovApiEntityResourceBase {

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
    // Load and return all QA nodes.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery();
    // The machine name for this entity is `q_a`.
    $query->condition('type', 'q_a')->condition('status', TRUE);
    $qa_nids = $query->execute();
    /** @var \Drupal\node\NodeInterface[] $qas */
    $qas = [];
    if (count($qa_nids)) {
      $qas = $node_storage->loadMultiple(array_values($qa_nids));
    }
    foreach ($qas as $entity) {
      $resource_object = NodeQa::buildResourceObject($entity, $resource_type);
      $this->addResourceObject($resource_object);
      $this->addCacheableDependency($entity);
    }
  }

}
