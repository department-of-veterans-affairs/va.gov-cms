<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\migration_tools\Message;
use Drupal\va_gov_migrate\AnomalyMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\va_gov_migrate\ParagraphMigrator;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\node\Entity\Node;

/**
 * Add paragraphs to node after save.
 *
 * @package Drupal\va_gov_migrate\EventSubscriber
 */
class PostRowSave implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE] = 'onMigratePostRowSave';
    return $events;
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
    if (!empty($event->getRow()->getSourceProperty('nav_linkslist'))) {
      AnomalyMessage::make(AnomalyMessage::MAJOR_LINKS,
        $event->getRow()->getSourceProperty('title'),
        $event->getRow()->getSourceIdValues()['url']);
    }

    switch ($event->getMigration()->id()) {
      case 'va_benefits_records':
      case 'va_new_hubs':
      case 'va_new_pages':
        $migrator->process('related_links', 'field_related_links');
        $migrator->process('featured_content', 'field_featured_content');
        $migrator->process(['body', 'nav_linkslist'], 'field_content_block');
        $this->setChangedDate($event);
        $this->setNodeAlias($event);
        break;

      case 'va_alert_block':
        $migrator->process('alert_body', 'field_alert_content');
        break;

      case 'va_hub':
      case 'va_new_landing_pages':
        $migrator->process('related_links', 'field_related_links');
        $migrator->process('hub_links', 'field_spokes');
        $this->setChangedDate($event);
        $this->setNodeAlias($event);
        break;

      case 'va_promo':
        $migrator->process('body', 'field_promo_link');
        break;

      case 'va_benefits_menu':
      case 'va_main_menu':
      case 'va_benefits_records_menu':
        $this->setMenuParent($event);
        break;
    }

    // va_gov_migrate.anomaly is an array of reported anomalies so we don't
    // report the same anomaly twice for the same page.
    \Drupal::state()->delete('va_gov_migrate.anomaly');
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
      AnomalyMessage::make('Lastupdate', $event->getRow()->getSourceProperty('title'), $event->getRow()->getSourceProperty('url'));
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
