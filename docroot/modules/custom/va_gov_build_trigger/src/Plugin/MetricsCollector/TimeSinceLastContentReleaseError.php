<?php

namespace Drupal\va_gov_build_trigger\Plugin\MetricsCollector;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Expose a metric for time since last content release error.
 *
 * @MetricsCollector(
 *   id = "va_gov_time_since_last_content_release_error",
 *   title = @Translation("Time since last content release error"),
 *   description = @Translation("Time since last content release error")
 * )
 */
class TimeSinceLastContentReleaseError extends BaseContentReleaseMetricsCollector {

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * TimeSinceLastContentRelease constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $state);
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function calculate() : int {
    $last_error_time = $this->state->get(ReleaseStateManager::LAST_RELEASE_ERROR_KEY);
    $now = $this->time->getCurrentTime();

    return $now - $last_error_time;
  }

}
