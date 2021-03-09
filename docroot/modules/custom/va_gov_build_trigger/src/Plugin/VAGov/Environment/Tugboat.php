<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\Command\CommandRunner;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\TugboatBuildTriggerForm;
use Drupal\va_gov_build_trigger\FrontendBuild\Command\Traits\Exportable as CommandExportable;

/**
 * Tugboat Plugin for Environment.
 *
 * @Environment(
 *   id = "tugboat",
 *   label = @Translation("tugboat")
 * )
 */
class Tugboat extends EnvironmentPluginBase {
  use CommandRunner;
  use CommandExportable;

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE) : void {
    if ($full_rebuild && $this->getWebBuildCommandBuilder()->useContentExport()) {
      $this->queue->enqueueCommands([
        $this->getExportCommand(),
      ]);
    }

    // A new command variable since the rebuild commands has been queued.
    $commands = $this->getWebBuildCommandBuilder()->buildCommands($front_end_git_ref);
    $this->queue->enqueueCommands($commands);

    $this->messenger()->addStatus('A request to rebuild the front end has been submitted.');
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild() : bool {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getBuildTriggerFormClass() : string {
    return TugboatBuildTriggerForm::class;
  }

}
