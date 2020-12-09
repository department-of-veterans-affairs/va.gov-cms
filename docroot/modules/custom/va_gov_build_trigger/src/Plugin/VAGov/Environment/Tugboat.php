<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_content_export\ExportCommand\CommandRunner;

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

  /**
   * {@inheritDoc}
   */
  public function getWebUrl(): string {
    // @TODO figure this out...
    return \Drupal::request()->getBasePath() . '/static';
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(): void {
    $commands = [
      'cd /app && COMPOSER_HOME=/var/www/.composer /usr/local/bin/composer va:web:build',
    ];

    $messages = $this->runCommands($commands);
    foreach ($messages as $message) {
      $this->messenger()->addMessage($message);
    }

  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild(): bool {
    return FALSE;
  }

}
