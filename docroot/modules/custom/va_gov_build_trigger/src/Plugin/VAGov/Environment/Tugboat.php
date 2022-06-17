<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\TugboatBuildTriggerForm;

/**
 * Tugboat Plugin for Environment.
 *
 * @Environment(
 *   id = "tugboat",
 *   label = @Translation("tugboat")
 * )
 */
class Tugboat extends EnvironmentPluginBase {

  /**
   * {@inheritDoc}
   */
  public function getBuildTriggerFormClass() : string {
    return TugboatBuildTriggerForm::class;
  }

  /**
   * {@inheritDoc}
   */
  public function shouldDisplayBuildDetails() : bool {
    return TRUE;
  }

}
