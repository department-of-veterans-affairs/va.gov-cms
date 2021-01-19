<?php

namespace Traits;

use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;

/**
 * Provide methods for management of content created for testing.
 *
 * This trait is meant to be used only by test classes.
 */
trait ContentTrait {

  /**
   * Get test content, find by some string in the title.
   *
   * @param string $type
   *   Content type.
   * @param string $string
   *   Title of Content.
   * @param int $number
   *   Default: -1 (the default) is equal to all.
   */
  public function getTestContentByString($type, $string, $number = -1) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', $type)
      ->condition('title', $string, 'CONTAINS')
      ->condition('status', NodeInterface::PUBLISHED);

    if ($number != -1) {
      $query->range(0, $number);
    }
    return array_values($query->execute());
  }

  /**
   * Get test content (nid) by title.
   *
   * @param string $title
   *   Content title.
   */
  public function getTestContentNidByTitle($title) {
    $query = \Drupal::entityQuery('node')->condition('title', $title);

    $query->range(0, 1);
    $results = $query->execute();

    if (empty($results)) {
      throw new \Exception('The node "' . $title . '" does not exist.');
    }

    return array_shift($results);
  }

  /**
   * Get test term (tid) by title.
   *
   * @param string $title
   *   Term title.
   */
  public function getTestContentTidByTitle($title) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $title]);
    $term = reset($term);
    $tid = $term->id();

    if (empty($tid)) {
      throw new \Exception('The term "' . $title . '" does not exist.');
    }

    return $tid;
  }

  /**
   * Delete content created within a step.
   *
   * @param int $nid
   *   Node ID.
   */
  public static function deleteNode($nid) {
    $node = Node::load($nid);
    $node->delete();
  }

  /**
   * Get most recent node id.
   *
   * * @param int $nid
   *   Node ID.
   */
  public static function getLastCreatedNodeId() {
    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->addField('nfd', 'nid');
    $query->range(0, 1);
    $query->orderBy("nid", 'DESC');
    $nid = $query->execute()->fetchField();

    return $nid;
  }

}
