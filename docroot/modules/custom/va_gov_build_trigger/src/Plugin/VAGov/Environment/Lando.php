<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Plugin\QueueWorker\WebBuildQueueWorker;
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
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->queue = \Drupal::queue(WebBuildQueueWorker::QUEUE_NAME);
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(): void {
    $commands = [
      'cd /app && COMPOSER_HOME=/var/www/.composer /usr/local/bin/composer va:web:build',
    ];

    $this->queue->createQueue();
    $this->queue->createItem($commands);

    $this->messenger()->addStatus('A request to rebuild the front end has been submitted.');
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild(): bool {
    return FALSE;
  }

}
