<?php

namespace Drupal\va_gov_workflow_assignments\Service;

use Drupal\Core\Database\Connection;
use Drupal\node\NodeInterface;

/**
 * Class EditorialWorkflowContentRepository.
 */
class EditorialWorkflowContentRepository implements EditorialWorkflowContentRepositoryInterface {

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new EditorialWorkflowContentRepository object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Drupal database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritDoc}
   */
  public function getLatestArchivedRevisionId(NodeInterface $node) : int {
    $query = $this->database->select('content_moderation_state_field_revision', 'cmr')
      ->condition('content_entity_id', $node->id(), '=')
      ->condition('moderation_state', 'archived', '=')
      ->fields('cmr', ['content_entity_revision_id'])
      ->orderBy('content_entity_revision_id', 'DESC')
      ->range(0, 1);
    $result = $query->execute()->fetchField();
    return $result ?? 0;
  }

  /**
   * {@inheritDoc}
   */
  public function getLatestPublishedRevisionId(NodeInterface $node) : int {
    $query = $this->database->select('content_moderation_state_field_revision', 'cmr')
      ->condition('content_entity_id', $node->id(), '=')
      ->condition('moderation_state', 'published', '=')
      ->fields('cmr', ['content_entity_revision_id'])
      ->orderBy('content_entity_revision_id', 'DESC')
      ->range(0, 1);
    $result = $query->execute()->fetchField();
    return $result ?? 0;
  }

}
