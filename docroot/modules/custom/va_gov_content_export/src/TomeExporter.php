<?php

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
   * The BreadcrumbEntity Manager.
   *
   * @var \Drupal\va_gov_content_export\AddBreadcrumbToEntity
   */
  protected $addBreadcrumbToEntity;

  /**
   * The ListDataCompiler Service.
   *
   * @var \Drupal\va_gov_content_export\ListDataCompiler
   */
  protected $listDataCompiler;

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
   * @param \Drupal\va_gov_content_export\AddBreadcrumbToEntity $add_breadcrumb_to_entity
   *   The BreadcrumbEntity Manager.
   * @param \Drupal\va_gov_content_export\ListDataCompiler $list_data_compiler
   *   The list data compiler service.
   */
  public function __construct(
    StorageInterface $content_storage,
    Serializer $serializer,
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $event_dispatcher,
    AccountSwitcherInterface $account_switcher,
    FileSyncInterface $file_sync,
    AddBreadcrumbToEntity $add_breadcrumb_to_entity,
    ListDataCompiler $list_data_compiler
  ) {
    parent::__construct($content_storage, $serializer, $entity_type_manager,
      $event_dispatcher, $account_switcher, $file_sync);

    $this->addBreadcrumbToEntity = $add_breadcrumb_to_entity;
    $this->listDataCompiler = $list_data_compiler;
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

    // We override all of the parent export to not create the index file.
    $this->switchToAdmin();
    $this->addBreadcrumbToEntity->alterEntity($entity);
    $this->listDataCompiler->updateLists($entity, $this);
    $data = $this->serializer->normalize($entity, 'json');
    $this->contentStorage->write(TomeSyncHelper::getContentName($entity), $data);

    if ($entity instanceof FileInterface) {
      $this->fileSync->exportFile($entity);
    }
    $event = new ContentCrudEvent($entity);
    $this->eventDispatcher->dispatch(TomeSyncEvents::EXPORT_CONTENT, $event);
    $this->switchBack();
  }

  /**
   * {@inheritdoc}
   *
   * Overriding to remove the updating of the index file.
   */
  public function deleteContentExport(ContentEntityInterface $entity) {
    // It would be cool if hook_entity_translation_delete() is invoked for
    // every translation of an entity when it's deleted. But it isn't. :-(.
    foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
      $this->contentStorage->delete(TomeSyncHelper::getContentName($entity->getTranslation($langcode)));
    }
    if ($entity instanceof FileInterface) {
      $this->fileSync->deleteFileExport($entity);
    }
    $event = new ContentCrudEvent($entity);
    $this->eventDispatcher->dispatch(TomeSyncEvents::DELETE_CONTENT, $event);
  }

}
