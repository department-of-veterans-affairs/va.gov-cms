<?php

namespace Drupal\va_gov_build_trigger\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ContentReleaseStatusBlock' block.
 *
 * @Block(
 *  id = "content_release_status_block",
 *  admin_label = @Translation("Content release status"),
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
   * EnvironmentDiscovery Service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->database = $container->get('database');
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->environmentDiscovery = $container->get('va_gov.build_trigger.environment_discovery');
    $instance->linkGenerator = $container->get('link_generator');
    $instance->urlGenerator = $container->get('url_generator');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $job = $this->getCommandRunnerJob();

    if (!$job) {
      return $build;
    }

    $table = [
      '#type' => 'table',
      '#header' => [
        '',
        $this->t('Status'),
        $this->t('Created'),
        $this->t('Processed'),
      ],
      '#rows' => [
        $this->buildTableRow($job),
      ],
    ];

    $build['#attached']['library'][] = 'va_gov_build_trigger/content_release_status_block';
    $build['#attached']['drupalSettings']['contentReleaseStatusBlock'] = [
      'blockRefreshPath' => $this->urlGenerator->generateFromRoute('va_gov_build_trigger.content_release_status_block_controller_get_block'),
    ];

    $table = $this->addLogLinks($table);

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

    $row[] = $this->getStatusIcon($job->state);
    $row[] = $this->getHumanReadableStatus($job->state);
    $row[] = $job->available ? $this->dateFormatter->format($job->available, 'standard') : '';
    $row[] = $job->processed ? $this->dateFormatter->format($job->processed, 'standard') : '';

    return $row;
  }

  /**
   * Add log links to table.
   *
   * @param array $table
   *   Drupal table render array.
   *
   * @return array
   *   Drupal table render array.
   */
  private function addLogLinks(array $table) : array {
    if (
      $this->environmentDiscovery->isTugboat() &&
      $tugboat_environment_id = $this->environmentDiscovery->getTugboatBuildEnvironmentId()
    ) {
      $log_url = Url::fromUri(
        "https://tugboat.vfs.va.gov/log/{$tugboat_environment_id}",
        [
          'attributes' => [
            '_target' => 'blank',
          ],
        ]
      );
      $log_link = $this->linkGenerator->generate(
        $this->t('View logs'),
        $log_url
      );

      $table['#header'][] = $this->t('Logs');
      $table['#rows'][] = $log_url;
    }

    return $table;
  }

  /**
   * Get the status icon for an advancedqueue job state.
   *
   * @param string $state
   *   Advancedqueue job state.
   *
   * @return array
   *   Table cell array with job status icon.
   */
  private function getStatusIcon(string $state) : array {
    $class = '';
    $icon = '';

    switch ($state) {
      case 'queued':
        $icon = '🕐';
        break;

      case 'processing':
        $class = 'status-animated';
        $icon = '🔨';
        break;

      case 'success':
        $icon = '✅';
        break;

      case 'failure':
        $icon = '❌';
        break;

      default:
        $icon = '❓';
        break;
    }

    return [
      'data' => $icon,
      'class' => $class,
    ];
  }

  /**
   * Get the human readable status for an advancedqueue job state.
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
      'class' => "status-{$state} ajax-progress-throbber",
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
      ->orderBy('available', 'DESC')
      ->execute()
      ->fetchObject();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
