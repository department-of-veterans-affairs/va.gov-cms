<?php

namespace Drupal\va_gov_content_export;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\tome_sync\Exporter;
use Drupal\tome_sync\FileSyncInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Exporter class for Tome.
 *
 * Overridden to exclude more types of entities.
 */
class TomeExporter extends Exporter {

  /**
   * File System.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * An array of excluded entity types.
   *
   * @var string[]
   */
  protected static $excludedTypes = [
    'content_moderation_state',
    'crop',
    'node.documentation_page',
    'path_alias',
    'site_alert',
    'user_history',
    'user_role',
    'user',
  ];

  /**
   * Add breadcrumb to Entity.
   *
   * @var \Drupal\va_gov_content_export\AddBreadcrumbToEntity
   */
  protected $addBreadcrumbToEntity;

  /**
   * Creates an Exporter object.
   *
   * This was overridden to allow the file system to be injected.
   *
   * @param \Drupal\Core\Config\StorageInterface $content_storage
   *   The target content storage.
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   The serializer.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The account switcher.
   * @param \Drupal\tome_sync\FileSyncInterface $file_sync
   *   The file sync service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system interface.
   * @param \Drupal\va_gov_content_export\AddBreadcrumbToEntity $addBreadcrumbToEntity
   *   The BreadcrumbEntity Manager.
   */
  public function __construct(
    StorageInterface $content_storage,
    Serializer $serializer,
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $event_dispatcher,
    AccountSwitcherInterface $account_switcher,
    FileSyncInterface $file_sync,
    FileSystemInterface $file_system,
    AddBreadcrumbToEntity $addBreadcrumbToEntity
  ) {
    parent::__construct($content_storage, $serializer, $entity_type_manager,
      $event_dispatcher, $account_switcher, $file_sync);

    $this->fileSystem = $file_system;
    $this->addBreadcrumbToEntity = $addBreadcrumbToEntity;
  }

  /**
   * {@inheritDoc}
   */
  public function exportContent(ContentEntityInterface $entity) {
    $type = $entity->getEntityTypeId();
    // If it's a node, attach the bundle.
    $type = ($type === 'node') ? "{$type}.{$entity->bundle()}" : $type;
    if (in_array($type, static::$excludedTypes, TRUE)) {
      return;
    }

    $this->addBreadcrumbToEntity->alterEntity($entity);
    parent::exportContent($entity);
  }

  /**
   * Acquires a lock for writing to the index.
   *
   * @return resource
   *   A file pointer resource on success.
   *
   * @throws \Exception
   *   Throws an exception when the index file cannot be written to.
   *
   * @TODO rework this error logic since this can cause a node not to save.
   */
  protected function acquireContentIndexLock() {
    $destination = $this->getContentIndexFilePath();
    $directory = dirname($destination);
    // Overridden to allow the drupal file system to create the directory.
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $handle = fopen($destination, 'c+');
    if (!flock($handle, LOCK_EX)) {
      throw new \Exception('Unable to acquire lock for the index file.');
    }
    return $handle;
  }

}
