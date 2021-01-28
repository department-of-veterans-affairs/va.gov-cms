<?php

namespace Drupal\va_gov_build_trigger\Plugin\Block;

use Drupal\advancedqueue\Job;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\va_gov_build_trigger\Plugin\AdvancedQueue\JobType\WebBuildJobType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ContentReleaseStatusBlock' block.
 *
 * @Block(
 *  id = "content_release_status_block",
 *  admin_label = @Translation("Recent updates"),
 * )
 */
class ContentReleaseStatusBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

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
   * EnvironmentDiscovery Service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    $instance->database = $container->get('database');
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->environmentDiscovery = $container->get('va_gov.build_trigger.environment_discovery');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $table = [
      '#type' => 'table',
      '#attributes' => ['class' => ['content-release-status-block']],
      '#header' => [
        $this->t('Status'),
        $this->t('Frontend Version'),
        $this->t('Started'),
        $this->t('Finished'),
      ],
    ];

    if ($this->shouldDisplayLogColumn()) {
      $table['#header'][] = $this->t('Logs');
    }

    $jobs = $this->getCommandRunnerJobs();

    if (count($jobs) === 0) {
      $table['#rows'] = [
        [['data' => $this->t('No recent updates'), 'colspan' => 5]],
      ];
      $build['content_release_status_block'] = $table;

      return $build;
    }

    foreach ($jobs as $job) {
      $table['#rows'][] = $this->buildTableRow($job);
    }

    $build['#attached']['library'][] = 'va_gov_build_trigger/content_release_status_block';
    $build['#attached']['drupalSettings']['contentReleaseStatusBlock'] = [
      'blockRefreshPath' => Url::fromRoute(
        'va_gov_build_trigger.content_release_status_block_controller_get_block'
      )->toString(),
    ];

    $build['content_release_status_block'] = $table;

    return $build;
  }

  /**
   * Build a queue status table row array.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   Advancedqueue Job.
   *
   * @return array
   *   Drupal table row array.
   */
  protected function buildTableRow(Job $job) : array {
    $row = [];

    $row[] = $this->getStatusCell($job);
    $row[] = $this->getFrontEndVersionCell($job);
    $row[] = $job->getAvailableTime() ? $this->dateFormatter->format($job->getAvailableTime(), 'standard') : '';
    $row[] = $job->getProcessedTime() ? $this->dateFormatter->format($job->getProcessedTime(), 'standard') : '';

    // The log page will show an error if there are no log messages,
    // so only show it once the job is being processed.
    if ($this->shouldDisplayLogColumn()) {
      $row[] = $job->getState() !== Job::STATE_QUEUED ? $this->getLogLink() : '';
    }

    return $row;
  }

  /**
   * Determine whether the log column should be displayed.
   *
   * @return bool
   *   Whether or not the log column should be displayed.
   */
  protected function shouldDisplayLogColumn() : bool {
    return in_array('administrator', $this->currentUser->getRoles());
  }

  /**
   * Add log links to table.
   *
   * @return array
   *   Link to dblog, filtered to web build messages.
   */
  protected function getLogLink() : array {
    $log_url = Url::fromRoute(
      "dblog.overview",
      [],
      [
        'attributes' => [
          'target' => '_blank',
        ],
        'query' => [
          'type[]' => WebBuildJobType::QUEUE_ID,
        ],
      ]
    );

    return [
      'data' => [
        '#title' => $this->t('View logs'),
        '#type' => 'link',
        '#url' => $log_url,
      ],
    ];
  }

  /**
   * Get the status cell for an advancedqueue job state.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   Advancedqueue Job.
   *
   * @return array
   *   Table cell array.
   */
  protected function getStatusCell(Job $job) : array {
    $icon = $this->getStatusIcon($job);
    $icon_class = 'job-status-icon';
    if ($job->getState() === Job::STATE_PROCESSING) {
      $icon_class .= ' status-animated';
    }
    $icon_html = "<span class='{$icon_class}'>{$icon}</span>";

    $status = $this->getHumanReadableStatus($job);

    return ['data' => ['#markup' => "{$icon_html} {$status}"]];
  }

  /**
   * Get the table cell for the front end version.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   Advancedqueue Job.
   *
   * @return array
   *   Table cell array.
   */
  protected function getFrontEndVersionCell(Job $job) : array {
    $payload = json_decode($job->getPayload());

    if (preg_match('/rm -fr docroot/', $payload->commands[0], $matches)) {
      return ['data' => ['#markup' => '[default]']];
    }

    if (preg_match('/origin\/([^\/ ]*)$/', $payload->commands[1], $matches)) {
      $branch = $matches[1];
      return ['data' => ['#markup' => "Branch: {$branch}"]];
    }

    if (preg_match('/git fetch origin pull\/([0-9]*)\/head/', $payload->commands[1], $matches)) {
      $pr = $matches[1];
      return ['data' => ['#markup' => "Pull request: #{$pr}"]];
    }

    return [];
  }

  /**
   * Get the status icon for an advancedqueue job state.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   Advancedqueue Job.
   *
   * @return string
   *   Job status icon.
   */
  protected function getStatusIcon(Job $job) : string {
    $icon = '';

    switch ($job->getState()) {
      case Job::STATE_QUEUED:
        $icon = '🕐';
        break;

      case Job::STATE_PROCESSING:
        $icon = '🔨';
        break;

      case Job::STATE_SUCCESS:
        $icon = '✅';
        break;

      case Job::STATE_FAILURE:
        $icon = '❌';
        break;

      default:
        $icon = '❓';
        break;
    }

    return $icon;
  }

  /**
   * Get the human readable status for an advancedqueue job state.
   *
   * @param \Drupal\advancedqueue\Job $job
   *   Advancedqueue Job.
   *
   * @return string
   *   Human-readable job status.
   */
  protected function getHumanReadableStatus(Job $job) : string {
    $status = '';
    $state = $job->getState();

    switch ($state) {
      case Job::STATE_QUEUED:
        $status = $this->t('Pending');
        break;

      case Job::STATE_PROCESSING:
        $status = $this->t('In Progress');
        break;

      case Job::STATE_SUCCESS:
        $status = $this->t('Success');
        break;

      case Job::STATE_FAILURE:
        $status = $this->t('Error');
        break;

      default:
        $status = $this->t('Unknown');
        break;
    }

    return $status;
  }

  /**
   * Get content release build jobs.
   *
   * @return array[\Drupal\advancedqueue\Job]
   *   Array of Jobs.
   */
  protected function getCommandRunnerJobs() : array {
    $jobs = [];

    $result = $this->database->select('advancedqueue', 'aq')
      ->condition('aq.queue_id', 'command_runner')
      ->fields('aq')
      ->range(0, 10)
      ->orderBy('available', 'DESC')
      ->execute();

    while ($record = $result->fetchAssoc()) {
      $jobs[] = new Job($record);
    }

    return $jobs;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
