<?php

namespace Drupal\va_gov_api\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Theme\PageAttachmentsEvent;
use Drupal\core_event_dispatcher\PageHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Add JS to the OpenAPI UI selector page so only one schema is displayed.
 */
class AddJsEventSubscriber implements EventSubscriberInterface {

  /**
   * The request.
   */
  protected RequestStack $requestStack;

  /**
   * Constructor.
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PageHookEvents::PAGE_ATTACHMENTS => ['onPageAttachments'],
    ];
  }

  /**
   * Modify response to add JS script to a route.
   */
  public function onPageAttachments(PageAttachmentsEvent $event): void {
    if ($this->requestStack->getCurrentRequest()->get('_route') === 'openapi.downloads') {
      $attachments = &$event->getAttachments();
      $attachments['#attached']['library'][] = 'va_gov_api/json_schemas_table_filter';
    }
  }

}
