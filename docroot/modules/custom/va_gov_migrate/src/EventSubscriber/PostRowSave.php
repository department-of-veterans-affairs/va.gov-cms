<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migration_tools\Message;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\va_gov_migrate\ParagraphMigrator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add paragraphs to node after save.
 *
 * @package Drupal\va_gov_migrate\EventSubscriber
 */
class PostRowSave implements EventSubscriberInterface {

  /**
   * The forms migration id.
   */
  private const VA_FORM_MIGRATION_ID = 'va_node_form';

  /**
   * Destination config key for fields copied onto carried draft revisions.
   */
  private const FORWARD_REVISION_OVERWRITE_PROPERTIES = 'forward_revision_overwrite_properties';

  /**
   * Forward revision context captured before row save.
   *
   * @var array<int, array<string, int>>
   */
  protected array $forwardRevisionContext = [];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::PRE_ROW_SAVE] = ['onMigratePreRowSave', 100];
    $events[MigrateEvents::POST_ROW_SAVE] = ['onMigratePostRowSave', 100];
    return $events;
  }

  /**
   * Capture forward revision context before the destination save occurs.
   *
   * @param \Drupal\migrate\Event\MigratePreRowSaveEvent $event
   *   Information about the event that triggered this function.
   */
  public function onMigratePreRowSave(MigratePreRowSaveEvent $event) {
    if (!$this->isVaFormMigration($event->getMigration()->id())) {
      return;
    }

    $source_id = $event->getRow()->getSourceProperty('rowid');
    if (empty($source_id)) {
      return;
    }

    $destination_ids = $event->getMigration()->getIdMap()->lookupDestinationIds([$source_id]);
    $nid = (int) ($destination_ids[0][0] ?? 0);
    if (empty($nid)) {
      return;
    }

    $default_revision = Node::load($nid);
    if (!$default_revision instanceof NodeInterface) {
      return;
    }

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $latest_revision_id = (int) $node_storage->getLatestRevisionId($nid);
    if (empty($latest_revision_id) || $latest_revision_id === (int) $default_revision->getRevisionId()) {
      return;
    }

    $latest_revision = $node_storage->loadRevision($latest_revision_id);
    if (!$latest_revision instanceof NodeInterface || $latest_revision->isDefaultRevision()) {
      return;
    }

    $this->forwardRevisionContext[$nid] = [
      'revision_id' => $latest_revision_id,
    ];
  }

  /**
   * Perform Post Row Save events.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   Information about the event that triggered this function.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function onMigratePostRowSave(MigratePostRowSaveEvent $event) {
    $migrator = new ParagraphMigrator($event);

    switch ($event->getRow()->getDestinationProperty('type')) {
      case 'page':
        $migrator->process('related_links', 'field_related_links');
        $migrator->process('featured_content', 'field_featured_content');
        $migrator->process(['body', 'nav_linkslist'], 'field_content_block');
        $this->setChangedDate($event);
        $this->setNodeAlias($event);
        break;

      case 'alert':
        $migrator->process('alert_body', 'field_alert_content');
        break;

      case 'landing_page':
        $migrator->process('related_links', 'field_related_links');
        $migrator->process('hub_links', 'field_spokes');
        $this->setChangedDate($event);
        $this->setNodeAlias($event);
        break;

      case 'promo':
        $migrator->process('body', 'field_promo_link');
        break;

      default:
        if ($event->getRow()->getDestinationProperty('bundle') === 'menu_link_content') {
          $this->setMenuParent($event);
          break;
        }
    }

    $this->carryForwardVaFormDraftRevision($event);

    // va_gov_migrate.anomaly is an array of reported anomalies so we don't
    // report the same anomaly twice for the same page.
    \Drupal::state()->delete('va_gov_migrate.anomaly');
  }

  /**
   * Carry migration-owned form fields onto the latest draft lineage.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   Information about the event that triggered this function.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function carryForwardVaFormDraftRevision(MigratePostRowSaveEvent $event) {
    if (!$this->isVaFormMigration($event->getMigration()->id())) {
      return;
    }

    $nid = (int) ($event->getDestinationIdValues()[0] ?? 0);
    if (empty($nid)) {
      return;
    }

    $forward_revision_context = $this->forwardRevisionContext[$nid] ?? NULL;
    unset($this->forwardRevisionContext[$nid]);
    if (empty($forward_revision_context['revision_id'])) {
      return;
    }

    $overwrite_properties = $event->getMigration()->getDestinationConfiguration()[self::FORWARD_REVISION_OVERWRITE_PROPERTIES] ?? [];
    if (empty($overwrite_properties)) {
      return;
    }

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $default_revision = $node_storage->load($nid);
    $draft_revision = $node_storage->loadRevision($forward_revision_context['revision_id']);
    if (!$default_revision instanceof NodeInterface || !$draft_revision instanceof NodeInterface) {
      return;
    }

    foreach ($overwrite_properties as $property) {
      if ($default_revision->hasField($property) && $draft_revision->hasField($property)) {
        $draft_revision->set($property, $default_revision->get($property)->getValue());
      }
    }

    $draft_revision->setNewRevision(TRUE);
    $draft_revision->enforceIsNew(FALSE);
    $draft_revision->setSyncing(TRUE);
    $draft_revision->setValidationRequired(FALSE);
    $draft_revision->isDefaultRevision(FALSE);
    $draft_revision->setRevisionLogMessage('Draft revision carried forward after Forms DB migration.');
    $draft_revision->save();
  }

  /**
   * Determine whether the event belongs to the forms migration.
   *
   * @param string $migration_id
   *   The migration id.
   *
   * @return bool
   *   TRUE when the forms migration is running.
   */
  protected function isVaFormMigration(string $migration_id): bool {
    return $migration_id === self::VA_FORM_MIGRATION_ID;
  }

  /**
   * Set the changed (Updated) date for a node.
   *
   * This should be done after other post row save events so that they don't
   * update the changed date after it's been set.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The event record for the node.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setChangedDate(MigratePostRowSaveEvent $event) {
    $node = Node::load($event->getDestinationIdValues()[0]);
    $last_update = $event->getRow()->getSourceProperty('lastupdate');
    // Drupal doesn't seem to accept 0 here, so use 1 for pages without a
    // lastupdate field.
    if ($last_update == 0) {
      $last_update = 1;
    }
    $node->set('changed', $last_update);
    $node->save();
  }

  /**
   * Delete any existing alias for this node and set a new one from 'path'.
   *
   * Also turns off automatic URL alias generation for this node.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The postRowSave event.
   */
  public function setNodeAlias(MigratePostRowSaveEvent $event) {
    $alias = $event->getRow()->getSourceProperty('path');
    if (empty($alias)) {
      return;
    }

    $alias_storage_helper = \Drupal::service('pathauto.alias_storage_helper');
    $source = '/node/' . $event->getDestinationIdValues()[0];

    $alias_storage_helper->deleteBySourcePrefix($source);
    $alias_storage_helper->save([
      'source' => $source,
      'alias' => $alias,
      'language' => 'en',
    ]);

    // Tell pathauto not to override alias.
    $pathauto_store = \Drupal::keyValue('pathauto_state.node');
    $pathauto_store->set($event->getDestinationIdValues()[0], 0);
  }

  /**
   * Sets the parent item of the menu item being migrated.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The PostRowSave event.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function setMenuParent(MigratePostRowSaveEvent $event) {
    $parent_id = $event->getRow()->getSourceProperty('parent_id');
    if (!empty($parent_id)) {
      $parent_link_array = $event->getMigration()->getIdMap()->lookupDestinationIds([$parent_id]);
      if (empty($parent_link_array)) {
        Message::make("Couldn't find dest for @id", ['@id' => $parent_id], Message::ERROR);
      }
      else {
        $menu_link = MenuLinkContent::load($event->getDestinationIdValues()[0]);
        $parent_link = MenuLinkContent::load($parent_link_array[0][0]);
        if (empty($parent_link)) {
          Message::make("Couldn't find menu link for @id", ['@id' => $parent_link_array[0][0]], Message::ERROR);
        }
        else {
          $menu_link->set('parent', 'menu_link_content:' . $parent_link->get('uuid')->value);
          $menu_link->save();
        }
      }
    }
  }

}
