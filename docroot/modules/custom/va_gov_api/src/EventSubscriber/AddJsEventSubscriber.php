<?php

namespace Drupal\va_gov_api\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Add JS to the OpenAPI UI selector page so only one schema is displayed.
 */
class AddJsEventSubscriber implements EventSubscriberInterface {

  /**
   * The module handler.
   */
  private ModuleHandlerInterface $moduleHandler;

  /**
   * Constructor.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onResponse'],
    ];
  }

  /**
   * Modify response to add JS script.
   *
   * @throws \DOMException
   */
  public function onResponse(ResponseEvent $event): void {
    // Only run this code on the OpenAPI UI selector page.
    $request = $event->getRequest();
    $path = $request->getPathInfo();
    if ($event->getRequestType() == HttpKernelInterface::MAIN_REQUEST && $path === '/admin/config/services/openapi') {

      $script = file_get_contents(
        DRUPAL_ROOT . '/'
        . $this->moduleHandler->getModule('va_gov_api')->getPath()
        . '/js/json-schemas-table-filter.js');

      // Create a DOMDocument object and load the response content into it.
      $responseContent = $event->getResponse()->getContent();
      $dom = new \DOMDocument();
      // Need to suppress errors due to <nav> tags.
      libxml_use_internal_errors(TRUE);
      $dom->loadHTML($responseContent);
      libxml_clear_errors();

      // Add the script to the DOMDocument object before the closing body tag.
      $dom->getElementsByTagName('body')
        ->item(0)
        ->appendChild($dom->createElement('script', $script));

      // Save the DOMDocument object back into the response content.
      $responseContent = $dom->saveHTML();
      $event->getResponse()->setContent($responseContent);
    }
  }

}
