<?php

// @codingStandardsIgnoreFile

namespace Drupal\va_gov_content_export;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\file\FileInterface;
use Drupal\tome_sync\Event\ContentCrudEvent;
use Drupal\tome_sync\Event\TomeSyncEvents;
use Drupal\tome_sync\Exporter;
use Drupal\tome_sync\FileSyncInterface;
use Drupal\tome_sync\TomeSyncHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Exporter class for Tome.
 *
 * Overridden to exclude more types of entities.
 */
class TomeExporter extends Exporter {

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
   */
  public function __construct(
    StorageInterface $content_storage,
    Serializer $serializer,
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $event_dispatcher,
    AccountSwitcherInterface $account_switcher,
    FileSyncInterface $file_sync
  ) {
    parent::__construct($content_storage, $serializer, $entity_type_manager,
      $event_dispatcher, $account_switcher, $file_sync);
  }

  /**
   * {@inheritDoc}
   */
  public function exportContent(ContentEntityInterface $entity) {
    // Disable Tome Sync exporting content using this method until we can untangle
    // tome sync from deploy steps.
    return;
  }

  /**
   * {@inheritdoc}
   *
   * Overriding to remove the updating of the index file.
   */
  public function deleteContentExport(ContentEntityInterface $entity) {
    // Disable Tome Sync exporting content using this method until we can untangle
    // tome sync from deploy steps.
    return;
  }

}
