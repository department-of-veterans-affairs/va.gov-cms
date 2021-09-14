<?php

namespace Drupal\va_gov_backend\Plugin\MetricsCollector;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Site\Settings;
use Drupal\prometheus_exporter\Plugin\BaseMetricsCollector;
use PNX\Prometheus\Gauge;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Collects metrics for the active user count.
 *
 * The granularity of the active user count is limited to the session write
 * interval.
 *
 * @see \Drupal\user\EventSubscriber\UserRequestSubscriber
 *
 * @MetricsCollector(
 *   id = "va_gov_active_user_count",
 *   title = @Translation("Active user count"),
 *   description = @Translation("Active user count.")
 * )
 */
class ActiveUserCount extends BaseMetricsCollector implements ContainerFactoryPluginInterface {

  /**
   * The user type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * The settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settingsService;

  /**
   * UserCount constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_storage
   *   The user type storage.
   * @param \Drupal\Core\Site\Settings $settings
   *   The site settings.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $user_storage, Settings $settings) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userStorage = $user_storage;
    $this->settingsService = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('settings')
    );
  }

  /**
   * Gets a count for this metric.
   *
   * @return int
   *   The count of active users in the most recent session write interval.
   */
  protected function getCount() {
    $query = $this->userStorage->getQuery();
    $sessionWriteInterval = $this->settingsService->get('session_write_interval', 180);
    $sinceTime = time() - $sessionWriteInterval;
    $query
      ->accessCheck(TRUE)
      ->condition('access', $sinceTime, '>=');
    $count = $query->count()->execute();
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function collectMetrics() {
    $gauge = new Gauge($this->getNamespace(), 'total', $this->getDescription());
    $gauge->set($this->getCount());
    $metrics[] = $gauge;
    return $metrics;
  }

}
