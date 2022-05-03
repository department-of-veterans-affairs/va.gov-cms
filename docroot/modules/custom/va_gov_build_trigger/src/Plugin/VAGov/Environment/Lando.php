<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Form\LandoBuildTriggerForm;

/**
 * Lando Plugin for Environment.
 *
 * @Environment(
 *   id = "lando",
 *   label = @Translation("Lando")
 * )
 */
class Lando extends EnvironmentPluginBase {

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild() : void {
    // phpcs:ignore
    // See issue https://github.com/department-of-veterans-affairs/va.gov-cms/issues/8796
    $message = $this->t('Build not dispatched because the content build is not currently working on ddev.');
    $this->messenger()->addStatus($message);
    $this->logger->info($message);
    return;
    // parent::triggerFrontendBuild();
  }

  /**
   * {@inheritDoc}
   */
  public function getBuildTriggerFormClass() : string {
    return LandoBuildTriggerForm::class;
  }

  /**
   * {@inheritDoc}
   */
  public function shouldDisplayBuildDetails() : bool {
    return TRUE;
  }

}
