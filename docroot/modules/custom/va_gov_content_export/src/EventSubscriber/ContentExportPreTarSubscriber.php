<?php

namespace Drupal\va_gov_content_export\EventSubscriber;

use Drupal\Core\File\FileSystemInterface;
use Drupal\va_gov_content_export\Event\ContentExportPreTarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Logs the creation of a new node.
 */
class ContentExportPreTarSubscriber implements EventSubscriberInterface {

  /**
   * Adds the openApi schema.json to the content export directory.
   *
   * @param \Drupal\va_gov_content_export\Event\ContentExportPreTarEvent $event
   *   Event object passed on from ContentExportPreTarEvent.
   */
  public function addContentSchemaFile(ContentExportPreTarEvent $event) {
    $router = \Drupal::service('router.no_access_checks');
    $openapi_router = $router->match('openapi/jsonapi');
    $schema = new JsonResponse($openapi_router["openapi_generator"]->getSpecification());
    $file_path = $event->getTarPath();
    $file = "{$file_path}/meta/schema.json";
    // Write content to file.
    // Using the Drupal file system will throw an exception if this fails.
    $event->fileSystem->saveData($schema->getContent(), $file, FileSystemInterface::EXISTS_REPLACE);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ContentExportPreTarEvent::CONTENT_EXPORT_PRE_TAR_EVENT][] = ['addContentSchemaFile'];
    return $events;
  }

}
