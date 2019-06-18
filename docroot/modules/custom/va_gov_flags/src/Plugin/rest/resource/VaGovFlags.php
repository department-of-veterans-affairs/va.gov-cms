<?php

namespace Drupal\va_gov_flags\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "va_gov_flags",
 *   label = @Translation("Return list of flags"),
 *   uri_paths = {
 *     "canonical" = "/flags_list"
 *   }
 * )
 */
class VaGovFlags extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new DefaultRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('va_gov_flags'),
      $container->get('current_user')
    );
  }

  /**
   * Outputs flag status' at endpoint.
   */
  public function get() {
    // Grab our feature flag names.
    $flag_status = \Drupal::service('feature_toggle.feature_status');
    $dump = $flag_status->getStatus('flag2');
    $flag_config = \Drupal::config('feature_toggle.features');
    $flag_names = $flag_config->get();
    $flag_toggle = [];
    // Recurse through the names to get their status.
    foreach ($flag_names['features'] as $key => $flag) {
      $flag_toggle[$flag]['status'] = !empty($flag_status->getStatus($key))
      ? $flag_status->getStatus($key)
      : FALSE;
    }
    // Convert them to json.
    print json_encode($flag_toggle);
    exit();
  }

}
