<?php

namespace Drupal\va_gov_backend\Plugin\MetricsCollector;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Site\Settings;
use Drupal\prometheus_exporter\Plugin\BaseMetricsCollector;
use Drupal\workbench_access\WorkbenchAccessManager;
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
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The settings service.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settingsService;

  /**
   * The section IDs.
   *
   * @var string[]
   */
  protected $sectionIds;

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
   * @param \Drupal\Core\Site\Settings $settings
   *   The site settings.
   * @param \Drupal\workbench_access\WorkbenchAccessManager $workbench_access_manager
   *   The workbench access manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Connection $database,
    Settings $settings,
    WorkbenchAccessManager $workbench_access_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->settingsService = $settings;
    $sectionScheme = $entity_type_manager->getStorage('access_scheme')->load('section');
    $this->sectionIds = $workbench_access_manager->getAllSections($sectionScheme);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('settings'),
      $container->get('plugin.manager.workbench_access.scheme'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Gets a query of "real users".
   *
   * This should return the users who are not administrators, API users, or
   * "ghost" users (users with duplicate accounts).
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A query, not yet completed.
   */
  protected function getRealUserQuery() {
    $disallowedRoles = [
      'administrator',
      'content_api_consumer',
    ];
    $query = $this->database->select('users_field_data', 'ufd');
    // Email will lead the query to return 1 on non-prod environments, because
    // of database sanitization.  It should work as expected on prod.
    $query->addField('ufd', 'mail');
    $query->innerJoin('user__roles', 'ur', 'ufd.uid = ur.entity_id AND ur.deleted = 0');
    $query->innerJoin('users', 'u', 'ufd.uid = u.uid');
    $query->leftJoin('section_association__user_id', 'suid', 'u.uid = suid.user_id_target_id');
    $query->condition('ur.roles_target_id', $disallowedRoles, 'NOT IN');
    // $query->condition('suid.entity_id', $this->sectionIds, 'IN');
    $query->isNotNull('suid.entity_id');
    $query->isNotNull('ufd.access');
    $query->condition('status', 1);
    return $query;
  }

  /**
   * Gets a count of "real users".
   *
   * Total number of users who are not:
   * - administrators.
   * - API accounts, or.
   * - “ghost” accounts.
   *
   * @return int
   *   The count of real users.
   */
  protected function getCount() {
    $query = $this->getRealUserQuery();
    $count = $query->distinct()->countQuery()->execute()->fetchField();
    return $count;
  }

  /**
   * Gets a count of users active since a specified time.
   *
   * @param int $timestamp
   *   A cutoff timestamp for considering a user active.
   *
   * @return int
   *   The count of active users in the specified time interval.
   */
  protected function getCountSinceTime($timestamp) {
    $query = $this->getRealUserQuery();
    $query
      ->condition('access', $timestamp, '>=');
    $count = $query->distinct()->countQuery()->execute()->fetchField();
    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function collectMetrics() {
    $now = time();
    $gauge = new Gauge($this->getNamespace(), 'total', $this->getDescription());
    $sessionWriteInterval = $this->settingsService->get('session_write_interval', 180);
    $sessionWriteIntervalTimestamp = $now - $sessionWriteInterval;
    // 60 days in seconds
    $sixtyDaysAgoTimestamp = $now - (60 * 86400);
    // 180 days in seconds
    $sixMonthsAgoTimestamp = $now - (180 * 86400);
    $gauge->set($this->getCount());
    $gauge->set($this->getCountSinceTime($sessionWriteIntervalTimestamp), ['last' => 'session_write_interval']);
    $gauge->set($this->getCountSinceTime($sixtyDaysAgoTimestamp), ['last' => '60days']);
    $gauge->set($this->getCountSinceTime($sixMonthsAgoTimestamp), ['last' => '180days']);
    $metrics[] = $gauge;
    return $metrics;
  }

}
