<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\node\NodeInterface;
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
      $resource_object = $this->createQaResourceObject($entity, $resource_type);
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
  private function createQaResourceObject(NodeInterface $entity, ResourceType $resource_type) {
    /** @var \Drupal\taxonomy\TermInterface $section_term */
    $section_term = $entity->field_administration->entity;
    /** @var \Drupal\paragraphs\Entity\Paragraph $answer_entity */
    $answer_entity = $entity->field_answer->entity;

    $alert_field = $entity->field_alert_single->entity->field_alert_non_reusable_ref->entity->field_va_paragraphs->entity->field_wysiwyg->processed;

    $links = [];
    foreach ($entity->get('field_buttons') as $button_field) {
      /** @var \Drupal\paragraphs\Entity\Paragraph $button_paragraph */
      $button_paragraph = $button_field->entity;

      /** @var \Drupal\link\LinkItemInterface */
      $button_paragraph_link = $button_paragraph->field_button_link->first();
      $button_url = $button_paragraph_link->getUrl();
      if (!$button_url->isRouted()) {
        $url_string = $button_url->toString();
      }
      else {
        /** @var \Drupal\node\NodeInterface */
        $related_node = $this->entityTypeManager->getStorage('node')->load($button_url->getRouteParameters()['node']);
        // @phpstan-ignore-next-line
        $url_string = $related_node->path->alias;
      }

      $links[] = [
        'button_label' => $button_paragraph->field_button_label->value,
        'button_link' => $url_string,
      ];
    }

    $data = [
      'nid' => $entity->id(),
      'uuid' => $entity->uuid(),
      'langcode' => $entity->language()->getId(),
      'status' => $entity->isPublished(),
      'title' => $entity->label(),
      'moderation_state' => $entity->get('moderation_state')->getString(),
      'path' => $entity->get('path'),
      'section_name' => $section_term->label(),
      // @phpstan-ignore-next-line
      'answer_text' => $answer_entity->field_wysiwyg->processed,
      'alert_text' => $alert_field,
      'links' => $links,
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
