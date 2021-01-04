<?php

namespace Drupal\va_gov_build_trigger\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ContentReleaseStatusBlock' block.
 *
 * @Block(
 *  id = "content_release_status_block",
 *  admin_label = @Translation("Content release status block"),
 * )
 */
class ContentReleaseStatusBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->database = $container->get('database');
    $instance->dateFormatter = $container->get('date.formatter');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $job = $this->getCommandRunnerJob();
    kint($job);
    if (!$job) {
      return $build;
    }

    $table = [
      '#type' => 'table',
      '#header' => [
        $this->t('Status'),
        $this->t('Created'),
        $this->t('Processed'),
      ],
      '#rows' => [
        $this->buildTableRow($job),
      ],
    ];

    $build['#theme'] = 'content_release_status_block';
    $build['content_release_status_block'] = $table;

    return $build;
  }

  /**
   * Build a queue status table row array.
   *
   * @param object $job
   *   Advancedqueue job database row.
   *
   * @return array
   *   Drupal table row array.
   */
  private function buildTableRow(\stdClass $job) : array {
    $row = [];

    $row[] = $this->getHumanReadableStatus($job->state);
    $row[] = $job->available ? $this->dateFormatter->format($job->available, 'standard') : '';
    $row[] = $job->processed ? $this->dateFormatter->format($job->processed, 'standard') : '';

    return $row;
  }

  /**
   * Build a queue status table row array.
   *
   * @param string $state
   *   Advancedqueue job state.
   *
   * @return array
   *   Table cell array with human-readable job status.
   */
  private function getHumanReadableStatus(string $state) : array {
    $status = '';
    switch ($state) {
      case 'queued':
        $status = $this->t('Pending');
        break;

      case 'processing':
        $status = $this->t('In Progress');
        break;

      case 'success':
        $status = $this->t('Success');
        break;

      case 'failure':
        $status = $this->t('Error');
        break;

      default:
        $status = $this->t('Unknown');
        break;
    }

    return [
      'data' => $status,
      'class' => "status-{$state}",
    ];
  }

  /**
   * Get content release build job.
   *
   * @return object
   *   The job's database row object.
   */
  private function getCommandRunnerJob() : \stdClass {
    return $this->database->select('advancedqueue', 'aq')
      ->condition('aq.queue_id', 'command_runner')
      ->fields('aq')
      ->range(0, 1)
      ->execute()
      ->fetchObject();
  }

}
