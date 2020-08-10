<?php

namespace Drupal\va_gov_content_export\EventSubscriber;

use Drupal\Core\File\FileSystemInterface;
use Drupal\va_gov_content_export\Event\ContentExportPreTarEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

/**
 * Logs the creation of a new node.
 */
class ContentExportPreTarSubscriber implements EventSubscriberInterface {

  /**
   * File System.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Route Class.
   *
   * @var \Symfony\Component\Routing\Matcher\UrlMatcherInterface
   */
  private $routeMatcher;

  /**
   * ContentExportPreTarSubscriber constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   File System.
   * @param \Symfony\Component\Routing\Matcher\UrlMatcherInterface $routeMatcher
   *   Route Matcher.
   */
  public function __construct(FileSystemInterface $fileSystem, UrlMatcherInterface $routeMatcher) {
    $this->fileSystem = $fileSystem;
    $this->routeMatcher = $routeMatcher;
  }

  /**
   * Adds the openApi schema.json to the content export directory.
   *
   * @param \Drupal\va_gov_content_export\Event\ContentExportPreTarEvent $event
   *   Event object passed on from ContentExportPreTarEvent.
   */
  public function addContentSchemaFile(ContentExportPreTarEvent $event) {
    $openapi_router = $this->routeMatcher->match('openapi/jsonapi');
    $schema = new JsonResponse($openapi_router["openapi_generator"]->getSpecification());
    $output_dir = "{$event->getArchieArgs()->getCurrentWorkingDirectory()}{$event->getArchieArgs()->getArchiveDirectory()}";
    $file = "{$output_dir}/meta/schema.json";
    // Write content to file.
    // Using the Drupal file system will throw an exception if this fails.
    $this->fileSystem->saveData($schema->getContent(), $file, FileSystemInterface::EXISTS_REPLACE);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ContentExportPreTarEvent::CONTENT_EXPORT_PRE_TAR_EVENT][] = ['addContentSchemaFile'];
    return $events;
  }

}
