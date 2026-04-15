<?php

namespace Drupal\va_gov_batch\cbo_scripts;

require_once __DIR__ . '/../../../../../../scripts/content/script-library.php';

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\NodeInterface;

/**
 * For VACMS-23756.
 *
 * This script gathers all the dual-published 'Programs' pages for Lovell sites.
 * It sets the page to be a VA page and duplicates it for Tricare. Basically
 * we are un-dual-publishing the pages and making 2 separate pages for VA and
 * Tricare, which is the requested setup. Menu links need to be cleaned up
 * afterward.
 *
 * To run: drush codit-batch-operations:run DuplicateLovellProgramPages.
 */
class DuplicateLovellProgramPages extends BatchOperations implements BatchScriptInterface {

  const LOVELL_FEDERAL_SYSTEM_ID = '15007';
  const VA_SYSTEM_ID = '49451';
  const TRICARE_SYSTEM_ID = '49011';
  const BOTH_ID = '347';
  const VA_ID = '1040';
  const TRICARE_ID = '1039';

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return "Removes dual publishing then duplicates Lovell program pages";
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total Programs pages were duplicated.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $items = [];
    $parent_uuids = $this->getParentLinkUuids();

    if (empty($parent_uuids)) {
      return $items;
    }

    // Query for VAMC Detail Pages with the correct menu parent.
    $menu_storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $menu_query = $menu_storage->getQuery()
      ->condition('parent', $parent_uuids, 'IN')
      ->accessCheck(FALSE);
    $mids = $menu_query->execute();

    $nids = [];
    $menu_links = MenuLinkContent::loadMultiple($mids);
    foreach ($menu_links as $link) {
      $uri = $link->link->uri;
      if (preg_match('/entity:node\/(\d+)/', $uri, $matches)) {
        $nids[] = $matches[1];
      }
    }

    if (!empty($nids)) {
      foreach ($nids as $nid) {
        /** @var \Drupal\node\NodeInterface $node */
        $node = $node_storage->load($nid);
        // Check if node matches criteria.
        $office = $node->get('field_office')->target_id;
        $admin = $node->get('field_administration')->target_id;
        $published = $node->isPublished();
        if ($published
          && $office == self::LOVELL_FEDERAL_SYSTEM_ID
          && $admin == self::BOTH_ID
          && !in_array($nid, $items)
        ) {
          $items[] = $nid;
        }
      }
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    /** @var \Drupal\node\NodeInterface $node */
    $node = $node_storage->load($item);
    if (!$node) {
      return "Node $item not found.";
    }
    try {
      $this->updateNode($node, FALSE);
      $this->batchOpLog->appendLog(
        "Updated node {$node->getTitle()} (ID: {$node->id()}) for VA values.");

      // Duplicate node for TRICARE.
      $duplicate = $node->createDuplicate();
      $this->updateNode($duplicate, TRUE);
      $this->batchOpLog->appendLog(
        "Updating node {$duplicate->getTitle()} (ID: {$duplicate->id()}) for Tricare values.");

    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      $message = $e->getMessage();
      $this->batchOpLog->appendError('Could not update/duplicate node: ' . $message);
    }

    return "Node $item updated for VA and duplicated for TRICARE.";
  }

  /**
   * Gets the UUIDs of the Lovell Programs menu links.
   *
   * @return array
   *   The array of UUIDs.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getParentLinkUuids(): array {
    $menu_link_storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');

    // Get menu parent UUIDs.
    $target_titles = ['Programs - Tricare', 'Programs - VA'];
    $parent_uuids = [];

    $query = $menu_link_storage->getQuery()
      ->condition('title', $target_titles, 'IN')
      ->accessCheck(FALSE);
    $ids = $query->execute();

    if (!empty($ids)) {
      $links = $menu_link_storage->loadMultiple($ids);
      /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $link */
      foreach ($links as $link) {
        $parent_uuids[$link->get('title')->value] = 'menu_link_content:' . $link->uuid();
      }
    }
    return $parent_uuids;
  }

  /**
   * Updates a node's office and administration for VA or Tricare.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to update.
   * @param bool $tricare
   *   Whether this is a Tricare node or not.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function updateNode(NodeInterface $node, bool $tricare): void {

    /** @var \Drupal\node\NodeStorageInterface $node_storage */
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    // Update original node to VA values.
    $node->set('field_office', $tricare ? self::TRICARE_SYSTEM_ID : self::VA_SYSTEM_ID);
    $node->set('field_administration', $tricare ? self::TRICARE_ID : self::VA_ID);
    $revision_message = "Un-dual-publishing for VA and TRICARE split Programs pages.";

    $default_rev_id = $node->getRevisionId();
    $latest_revision_id = $node_storage->getLatestRevisionId($node->id());

    // Save forward revisions if they exist.
    if ($latest_revision_id > $default_rev_id) {
      /** @var \Drupal\node\NodeInterface $revision */
      $revision = $node_storage->loadRevision($latest_revision_id);
      $existing_message = $revision->getRevisionLogMessage() ?? '';
      save_node_revision($revision, $revision_message . ' - ' . $existing_message, FALSE);
    }
    save_node_revision($node, $revision_message);
  }

}
