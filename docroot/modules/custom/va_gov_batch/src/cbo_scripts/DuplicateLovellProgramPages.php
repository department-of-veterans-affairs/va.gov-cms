<?php

namespace Drupal\va_gov_batch\cbo_scripts;

require_once __DIR__ . '/../../../../../../scripts/content/script-library.php';

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\NodeInterface;
use Exception;

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
      $link_field = $link->get('link')->getValue();
      $uri = !empty($link_field[0]['uri']) ? $link_field[0]['uri'] : '';
      if (preg_match('/entity:node\/(\d+)/', $uri, $matches)) {
        $nids[] = $matches[1];
      }
    }

    if (!empty($nids)) {
      foreach ($nids as $nid) {
        $node = $node_storage->load($nid);
        if (!$node instanceof NodeInterface) {
          continue;
        }
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
    catch (InvalidPluginDefinitionException | PluginNotFoundException | EntityStorageException | Exception $e) {
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
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function updateNode(NodeInterface $node, bool $tricare): void {

    /** @var \Drupal\node\NodeStorageInterface $node_storage */
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    // Update original node to VA values.
    $node->set('field_office', $tricare ? self::TRICARE_SYSTEM_ID : self::VA_SYSTEM_ID);
    $node->set('field_administration', $tricare ? self::TRICARE_ID : self::VA_ID);

    $is_duplicate = $node->isNew();
    if (!$is_duplicate) {
      $revision_message = "Un-dual-publishing for VA and TRICARE split Programs pages.";

      $default_rev_id = $node->getRevisionId();
      $latest_revision_id = $node_storage->getLatestRevisionId($node->id());

      // Save forward revisions if they exist.
      if ($latest_revision_id > $default_rev_id) {
        /** @var \Drupal\node\NodeInterface $revision */
        $revision = $node_storage->loadRevision($latest_revision_id);
        $revision->set('field_office', $tricare ? self::TRICARE_SYSTEM_ID : self::VA_SYSTEM_ID);
        $revision->set('field_administration', $tricare ? self::TRICARE_ID : self::VA_ID);
        $existing_message = $revision->getRevisionLogMessage() ?? '';
        save_node_revision($revision, $revision_message . ' - ' . $existing_message, FALSE);
        $this->batchOpLog->appendLog('Saving forward revision for: ' . $node->getTitle());
      }
      save_node_revision($node, $revision_message);
      $this->batchOpLog->appendLog('Saving original node for: ' . $node->getTitle());
    } else {
      // Always save the duplicate node first.
      $node->save();
      $this->batchOpLog->appendLog('Saving duplicate node for: ' . $node->getTitle());
    }


    $this->setMenuLinks($tricare, $node);
    $this->setUrlAlias($tricare, $node);

  }

  /**
   * Update the URL alias for the node based on whether it's VA or Tricare.
   *
   * @param bool $tricare
   *   Whether its a tricare node or not.
   * @param NodeInterface $node
   *   The node to update
   *
   * @throws EntityStorageException
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  protected function setUrlAlias(bool $tricare, NodeInterface $node): void {
    $fac_type = ($tricare ? 'tricare' : 'va');
    $url_title = rawurlencode(strtr(strtolower($node->getTitle()), ' ', '_'));
    $new_alias = "/lovell-federal-health-care-$fac_type/programs-$fac_type/" . $url_title;

    // Remove all existing aliases for this node.
    $path_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    $aliases = $path_storage->loadByProperties(['path' => '/node/' . $node->id()]);
    foreach ($aliases as $alias) {
      $alias->delete();
    }

    // Set the new alias and disable Pathauto.
    if ($node->hasField('path')) {
      $node->set('path', ['alias' => $new_alias, 'pathauto' => 0]);
    }
    $node->save();
    $this->batchOpLog->appendLog('Set alias for node ' . $node->id() . ' to ' . $new_alias);
  }

  /**
   * Updates or creates a menu link for the node.
   *
   * @param bool $tricare
   *   Whether the node is Tricare or not.
   * @param NodeInterface $node
   *   The node being updated.
   *
   * @throws EntityStorageException
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  protected function setMenuLinks(bool $tricare, NodeInterface $node): void {
    $menu_link_storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $section_value = $tricare ? 'tricare' : 'va';

    // Update menu link for this node.
    $parent_uuids = $this->getParentLinkUuids();
    $this->batchOpLog->appendLog('Got parent link uuids of: ' . implode(', ', $parent_uuids));
    $parent_uuid = $tricare ? ($parent_uuids['Programs - Tricare'] ?? null) : ($parent_uuids['Programs - VA'] ?? null);

    $menu_link_entities = $menu_link_storage->loadByProperties([
      'link__uri' => 'entity:node/' . $node->id(),
    ]);
    $this->batchOpLog->appendLog('Found ' . count($menu_link_entities) . ' menu links for node ID ' . $node->id());

    // Always ensure a menu link exists for the duplicate node (or for any node if needed).
    // Only create a menu link if one does not already exist for this node.
    if (empty($menu_link_entities) && $parent_uuid) {
      // Use the entity_field.manager service to check for field_menu_section.
      $field_manager = \Drupal::service('entity_field.manager');
      $menu_link_fields = $field_manager->getFieldDefinitions('menu_link_content', 'menu_link_content');
      $menu_link_data = [
        'title' => $node->getTitle(),
        'link' => ['uri' => 'entity:node/' . $node->id()],
        'menu_name' => 'lovell-federal-health-care',
        'parent' => $parent_uuid,
        'enabled' => TRUE,
      ];

      if (isset($menu_link_fields['field_menu_section'])) {
        $menu_link_data['field_menu_section'] = $section_value;
      }
      $this->batchOpLog->appendLog('Creating menu link with data: ' . implode(', ', $menu_link_data));
      $menu_link = MenuLinkContent::create($menu_link_data);

      $menu_link->save();
      $this->batchOpLog->appendLog('Saving new menu link for: ' . $node->getTitle());
    } else {
      // If a menu link exists, update its parent to ensure correctness.
      foreach ($menu_link_entities as $menu_link) {
        if ($parent_uuid) {
          $menu_link->set('parent', $parent_uuid);
        }
        // Also update field_menu_section if present.
        if ($menu_link->hasField('field_menu_section')) {
          $menu_link->set('field_menu_section', $section_value);
        }
        $menu_link->save();
        $this->batchOpLog->appendLog('Updating existing menu link for: ' . $node->getTitle());
      }
    }
  }

}
