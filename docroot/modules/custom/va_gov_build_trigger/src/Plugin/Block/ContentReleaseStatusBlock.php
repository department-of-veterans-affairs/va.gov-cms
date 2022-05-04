<?php

namespace Drupal\va_gov_build_trigger\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\va_gov_build_trigger\Service\BuildRequester;

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
   * State service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Environment discovery service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state'),
      $container->get('date.formatter'),
      $container->get('va_gov.build_trigger.environment_discovery'),
    );
  }

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   The environment discovery service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    StateInterface $state,
    DateFormatterInterface $dateFormatter,
    EnvironmentDiscovery $environmentDiscovery
  ) {
    $this->configuration = $configuration;
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->state = $state;
    $this->dateFormatter = $dateFormatter;
    $this->environmentDiscovery = $environmentDiscovery;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $release_state = $this->state->get('va_gov_build_trigger.release_state', 'ready');
    $last_release = $this->state->get('va_gov_build_trigger.last_release_complete', 0);

    $items = [];

    // If the frontend has been built, display a link to the environment.
    $front_end_link = $this->t('Front end has not been built yet.');
    $front_end_description = $this->t('Once a release is completed successfully, this section will update with a link to the newly built VA.gov front end.');
    if ($last_release !== 0) {
      $target = $this->environmentDiscovery->getWebUrl();
      $target_url = Url::fromUri($target, ['attributes' => ['target' => '_blank']]);
      $front_end_link = Link::fromTextAndUrl($this->t('View front end'), $target_url);
      $front_end_description = $this->t('See how your content will appear to site visitors on the front end.');
    }

    $items['frontend_link'] = [
      'title' => $this->t('Front end link'),
      'description' => $front_end_description,
      'value' => $front_end_link,
    ];

    $items['release_state'] = [
      'title' => $this->t('Release state'),
      'value' => $this->getHumanReadableState($release_state),
    ];

    $items['last_release'] = [
      'title' => $this->t('Last release'),
      'value' => $this->formatTimestamp($last_release),
    ];

    if ($this->environmentDiscovery->shouldDisplayBuildDetails()) {
      $current_frontend_version = $this->state->get(BuildRequester::VA_GOV_FRONTEND_VERSION, '[default]');
      $build_log_link =

      $items['front_end_version'] = [
        'title' => $this->t('Front end version'),
        'value' => $current_frontend_version,
      ];

      // If the frontend has been built, display a link to the environment.
      $build_log_link = $this->t('No build log available');
      $build_log_description = $this->t('Once a release is completed successfully, this section will update with a link to the full log output of the content release (including a broken link report).');
      if ($last_release !== 0) {
        $target = $this->environmentDiscovery->getWebUrl();
        $target_url = Url::fromUserInput('/sites/default/files/build.txt', ['attributes' => ['target' => '_blank']]);
        $build_log_link = Link::fromTextAndUrl($this->t('Build log'), $target_url);
        $build_log_description = $this->t('View the full output of the last completed build process (including a broken link report).');
      }
      $items['build_log'] = [
        'title' => $this->t('Build log'),
        'description' => $build_log_description,
        'value' => $build_log_link,
      ];
    }

    $status = [
      '#theme' => 'status_report_grouped',
      '#grouped_requirements' => [
        [
          'title' => $this->t('Content release status'),
          'type' => 'content-release-status',
          'items' => $items,
        ],
      ],
    ];

    $build['#attached']['library'][] = 'va_gov_build_trigger/content_release_status_block';
    $build['#attached']['drupalSettings']['contentReleaseStatusBlock'] = [
      'blockRefreshPath' => Url::fromRoute(
        'va_gov_build_trigger.content_release_status_block_controller_get_block'
      )->toString(),
    ];

    $build['content_release_status_block'] = $status;

    return $build;
  }

  /**
   * Formats our internal state names for end-user consumption.
   *
   * @param string $state
   *   Internal content release state.
   *
   * @return string
   *   User-facing content release state.
   */
  protected function getHumanReadableState(string $state) : string {
    switch ($state) {
      case 'ready':
      case 'requested':
        return $this->t('Ready');

      case 'dispatched':
      case 'starting':
        return $this->t('Preparing');

      case 'inprogress':
        return $this->t('In Progress');

      case 'complete':
        return $this->t('Complete');
    }

    return 'unknown';
  }

  /**
   * Format a timestamp using the site standard date format and timezone.
   *
   * @param int $timestamp
   *   A unix timestamp.
   *
   * @return string
   *   A formatted date/time.
   */
  protected function formatTimestamp(int $timestamp) : string {
    if ($timestamp === 0) {
      return $this->t('Never');
    }

    return $this->dateFormatter->format($timestamp, 'standard');
  }

}
