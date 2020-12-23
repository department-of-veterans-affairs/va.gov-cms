<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use Drupal\va_gov_build_trigger\Plugin\QueueWorker\WebBuildQueueWorker;
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
      'cd /var/lib/tugboat && COMPOSER_HOME=/var/lib/tugboat /usr/local/bin/composer --no-cache va:web:build',
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
