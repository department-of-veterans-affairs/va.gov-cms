<?php

namespace Drupal\va_gov_api\EventSubscriber;

use Drupal\jsonapi\ResourceType\ResourceType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Disables access checks for menu_item and block_content in JSONAPI requests.
 */
class JsonApiAccessOverrideSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Run early in the request cycle, before access checks.
    return [
      KernelEvents::REQUEST => ['onRequest', 100],
    ];
  }

  /**
   * Disables access checks for specific entity types in JSONAPI requests.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function onRequest(RequestEvent $event): void {
    $request = $event->getRequest();

    // Only act on JSONAPI requests.
    if (strpos($request->getPathInfo(), '/jsonapi/') !== 0) {
      return;
    }

    // Get the resource type from the request attributes.
    $resource_type = $request->attributes->get('resource_type');

    if ($resource_type instanceof ResourceType) {
      $entity_type_id = $resource_type->getEntityTypeId();

      // Disable access checks for menu_link_content and block_content.
      if (in_array($entity_type_id, ['menu_link_content', 'block_content'])) {
        // Set a flag to bypass access checks.
        $request->attributes->set('_disable_route_requirements', TRUE);
      }
    }
  }

}
