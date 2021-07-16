<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\node\NodeInterface;

/**
 * Class for processing facility status to GovDelivery Bulletin.
 */
interface BuildFrontendInterface {

  /**
   * Triggers the appropriate frontend Build based on the environment.
   *
   * @param string $front_end_git_ref
   *   Front end git reference to build (branch name or PR number).
   * @param bool $full_rebuild
   *   Trigger a full front end rebuild.
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE) : void;

  /**
   * Set the config state of build pending.
   *
   * @param bool $state
   *   The state that should be set for build pending.
   */
  public function setPendingState(bool $state) : void;

  /**
   * Method to trigger a frontend build as the result of a save.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   */
  public function triggerFrontendBuildFromContentSave(NodeInterface $node);

}
