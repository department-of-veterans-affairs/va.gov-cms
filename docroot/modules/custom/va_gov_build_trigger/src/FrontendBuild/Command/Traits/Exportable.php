<?php

namespace Drupal\va_gov_build_trigger\FrontendBuild\Command\Traits;

use Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface;

/**
 * Add cms export compatibility.
 */
trait Exportable {

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
   * Get the command builder.
   *
   * @return \Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface
   *   The command builder.
   */
  abstract protected function getWebBuildCommandBuilder() : BuilderInterface;

}
