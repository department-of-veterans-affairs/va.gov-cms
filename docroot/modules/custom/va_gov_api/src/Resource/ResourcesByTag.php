<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\taxonomy\TermInterface;
use Drupal\va_gov_api\ResourceObjectBuilders\NodeQa;
use Symfony\Component\HttpFoundation\Request;

/**
 * Resource for returning a collection of 'resource' product nodes.
 *
 * Apologies for the naming collision. Currently limited to q_a nodes.
 */
class ResourcesByTag extends VaGovApiEntityResourceBase {

  /**
   * {@inheritDoc}
   */
  protected function collectResourceData(Request $request, ResourceType $resource_type) {
    // Doing this as a case/switch for now. There are almost certainly better
    // ways to do this.
    // This resource requires a tag. If it's not available, exit.
    if (!$this->getRouteParameter('resource_tag')) {
      // @todo This should throw an error response.
    }
    switch ($resource_type->getTypeName()) {
      case 'node--q_a':
        $this->collectQaDataByTag($request, $resource_type, $this->getRouteParameter('resource_tag'));
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
   * @param mixed $resource_tag
   *   The resource tag, if passed in.
   */
  private function collectQaDataByTag(Request $request, ResourceType $resource_type, $resource_tag) {
    if (!$resource_tag instanceof TermInterface) {
      return;
    }
    // Load any audience paragraphs where the resource_tag value is set.
    $paragraph_storage = $this->entityTypeManager->getStorage('paragraph');
    $paragraph_query = $paragraph_storage->getQuery();
    $paragraph_query->condition('type', 'audience_topics')->condition('status', TRUE);
    switch ($resource_tag->bundle()) {
      case 'topics':
        $paragraph_query->condition('field_topics', $resource_tag->id());
        break;

      case 'audience_beneficiaries':
        $paragraph_query->condition('field_audience_beneficiares', $resource_tag->id());
        break;

      case 'audience_non_beneficiaries':
        $paragraph_query->condition('field_non_beneficiares', $resource_tag->id());
        break;

      default:
        // We haven't found an appropriate set of paragraphs to filter on.
        return;
    }

    if ($audience_paragraphs = $paragraph_query->execute()) {
      // Load and return all QA nodes.
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery();
      // The machine name for this entity is `q_a`.
      $query->condition('type', 'q_a')
        ->condition('status', TRUE)
        ->condition('field_tags', array_values($audience_paragraphs), 'IN');
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

}
