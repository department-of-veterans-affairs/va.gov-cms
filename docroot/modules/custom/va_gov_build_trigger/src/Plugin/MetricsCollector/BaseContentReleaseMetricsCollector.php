<?php

namespace Drupal\va_gov_build_trigger\Plugin\MetricsCollector;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\prometheus_exporter\Plugin\BaseMetricsCollector;
use PNX\Prometheus\Gauge;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for content release metrics collectors.
 */
abstract class BaseContentReleaseMetricsCollector extends BaseMetricsCollector implements ContainerFactoryPluginInterface {

  /**
   * The name of the metric that will be exposed.
   *
   * @var string
   */
  protected $name = 'duration';

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * BaseContentReleaseMetricsCollector constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function collectMetrics() {
    $value = $this->calculate();

    $clock_gauge = new Gauge($this->getNamespace(), $this->name, $this->getDescription());
    $clock_gauge->set($value);

    $business_hours_gauge = new Gauge($this->getNamespace(), 'bizhours_' . $this->name, $this->getDescription());
    $business_hours_gauge->set($value % 3600);

    return [$clock_gauge, $business_hours_gauge];
  }

  /**
   * Calculate whatever metric is needed.
   *
   * @return int
   *   The number representing whatever metric the child class exposes.
   */
  abstract protected function calculate() : int;

}
