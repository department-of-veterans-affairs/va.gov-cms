<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_content_export\ExportCommand\CommandRunner;

/**
 * Lando Plugin for Environment.
 *
 * @Environment(
 *   id = "lando",
 *   label = @Translation("Lando")
 * )
 */
class Lando extends EnvironmentPluginBase {
  use CommandRunner;

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

    $this->logger->info('Front end has been rebuilt');
    $this->messenger()->addStatus('Front end site has been rebuilt');
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild(): bool {
    return FALSE;
  }

}
