<?php

namespace Drupal\va_gov_build_trigger;

/**
 * Add cms export compatibility.
 */
trait CommandExportable {

  /**
   * Get the command to run for a full export.
   *
   * @return string
   *   THe command to run for a full export.
   */
  protected function getExportCommand() : string {
    return $this->getWebBuildCommandBuilder()->buildComposerCommand(
      'va:web:export:content'
    );
  }

  /**
   * Get the WebBuildCommandBuilder class.
   *
   * @return \Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface
   *   The Web Build Command class.
   */
  abstract protected function getWebBuildCommandBuilder() : WebBuildCommandBuilderInterface;

}
