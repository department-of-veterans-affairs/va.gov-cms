<?php

namespace Drupal\va_gov_workflow_assignments\Service;

use Drupal\node\NodeInterface;

/**
 * Interface EditorialWorkflowContentRepositoryInterface.
 */
interface EditorialWorkflowContentRepositoryInterface {

  /**
   * Get the latest archived revision ID for a node.
   *
   * @return int
   *   Revision ID.
   */
  public function getLatestArchivedRevisionId(NodeInterface $node) : int;

  /**
   * Get the latest published revision ID for a node.
   *
   * @return int
   *   Revision ID.
   */
  public function getLatestPublishedRevisionId(NodeInterface $node) : int;

}
