<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\LocalBuildTriggerForm;

/**
 * Local Plugin for Environment.
 *
 * @Environment(
 *   id = "local",
 *   label = @Translation("Local")
 * )
 */
class Local extends EnvironmentPluginBase {

  /**
   * {@inheritDoc}
   */
  public function getBuildTriggerFormClass() : string {
    return LocalBuildTriggerForm::class;
  }

  /**
   * {@inheritDoc}
   */
  public function shouldDisplayBuildDetails() : bool {
    return TRUE;
  }

}
