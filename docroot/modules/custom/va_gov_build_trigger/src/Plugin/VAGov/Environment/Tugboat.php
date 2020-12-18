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
  public function triggerFrontendBuild(): void {
    $commands = [
      'cd /var/lib/tugboat && COMPOSER_HOME=/var/lib/tugboat /usr/local/bin/composer --no-cache va:web:build',
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
