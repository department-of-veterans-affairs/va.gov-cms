<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\node\NodeInterface;

/**
 * Class for dispatching frontend builds.
 */
interface BuildFrontendInterface {

  /**
   * Triggers the appropriate frontend Build based on the environment.
   *
   * @param string $front_end_git_ref
   *   Front end git reference to build (branch name or PR number).
   * @param bool $full_rebuild
   *   Trigger a full content export rebuild.
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
   * Check to see if this had a status or status info change.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   *
   * @return bool
   *   TRUE if there was a status related change, FALSE if there was not.
   */
  private function changedStatus(NodeInterface $node) : bool;

  /**
   * Gets the previously saved value of a field.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   * @param string $fieldname
   *   The machine name of the field to get.
   *
   * @return string
   *   The value of the field, or '' if not found.
   */
  private function getOriginalFieldValue(NodeInterface $node, $fieldname) : string;

  /**
   * Method to trigger a frontend build as the result of a save.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   */
  public function triggerFrontendBuildFromContentSave(NodeInterface $node) : void;

}
