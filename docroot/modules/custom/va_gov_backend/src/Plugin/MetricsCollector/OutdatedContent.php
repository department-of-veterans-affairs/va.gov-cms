<?php

namespace Drupal\va_gov_backend\Plugin\MetricsCollector;

use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\prometheus_exporter\Plugin\BaseMetricsCollector;
use PNX\Prometheus\Gauge;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Collects metrics for outdated content.
 *
 * @see \Drupal\user\EventSubscriber\UserRequestSubscriber
 *
 * @MetricsCollector(
 *   id = "va_gov_outdated_content",
 *   title = @Translation("Outdated content"),
 *   description = @Translation("Outdated content.")
 * )
 */
class OutdatedContent extends BaseMetricsCollector implements ContainerFactoryPluginInterface {

  /**
   * The user type storage.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * UserCount constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The Drupal database connection.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Connection $database
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * Gets a count of content not updated since the given timestamp.
   *
   * @param int $timestamp
   *   The timestamp to check against.
   *
   * @return int
   *   The count of stale content.
   */
  protected function getStaleContentCount($timestamp) {
    $excludedTypes = ['event', 'press_release', 'news_story'];
    $query = $this->database->select('node_field_data', 'node');
    $query->addField('node', 'nid');
    $query->leftJoin('node__field_last_saved_by_an_editor', 'last_saved', 'last_saved.entity_id = node.nid AND last_saved.deleted = 0 AND (last_saved.langcode = node.langcode OR last_saved.bundle = :bundle)', [':bundle' => 'page']);
    $query->condition('node.status', 1)
      ->condition('node.type', $excludedTypes, 'NOT IN')
      ->condition('last_saved.field_last_saved_by_an_editor_value', $timestamp, '<=')
      ->orderBy('last_saved.field_last_saved_by_an_editor_value', 'ASC');
    $nids = $query->execute()->fetchAllKeyed(0, 0);
    return count($nids);
  }

  /**
   * {@inheritdoc}
   */
  public function collectMetrics() {
    $metrics = [];
    $oneYearAgo = strtotime("-1 year");
    $gauge = new Gauge($this->getNamespace(), 'total', $this->getDescription());
    $gauge->set($this->getStaleContentCount(strtotime("-30 days", $oneYearAgo)), ['lastSaved' => '30DaysOverdue']);
    $gauge->set($this->getStaleContentCount(strtotime("-60 days", $oneYearAgo)), ['lastSaved' => '60DaysOverdue']);
    $gauge->set($this->getStaleContentCount(strtotime("-90 days", $oneYearAgo)), ['lastSaved' => '90DaysOverdue']);
    $allOverdue = $this->getStaleContentCount($oneYearAgo);
    $gauge->set($allOverdue, ['lastSaved' => 'AllOverdueContent']);
    $gauge->set($this->getStaleContentCount(strtotime("+30 days", $oneYearAgo)) - $allOverdue, ['lastSaved' => '30DaysUntilOverdue']);
    $gauge->set($this->getStaleContentCount(strtotime("+60 days", $oneYearAgo)) - $allOverdue, ['lastSaved' => '60DaysUntilOverdue']);
    $gauge->set($this->getStaleContentCount(strtotime("+90 days", $oneYearAgo)) - $allOverdue, ['lastSaved' => '90DaysUntilOverdue']);
    $metrics[] = $gauge;
    return $metrics;
  }

}
