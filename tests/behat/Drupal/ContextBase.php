<?php

namespace CustomDrupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines a base context implementation that most context classes will extend.
 */
abstract class ContextBase extends RawDrupalContext {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructs a ContextBase.
   */
  public function __construct() {
    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * @BeforeFeature @mock_va_gov_urls
   */
  static function enableVaGovBackendHttpClient()
  {
    \Drupal::service('module_installer')->install(['va_gov_backend_http_client']);
  }

  /**
   * @AfterFeature @mock_va_gov_urls
   */
  static function disableVaGovBackendHttpClient()
  {
    \Drupal::service('module_installer')->uninstall(['va_gov_backend_http_client']);
  }

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function entityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
   * Returns a "missing rows" label value for a given plural name.
   *
   * @param string $plural_name
   *   The plural name for rows indicating what they signify, e.g., "bundles" or
   *   "fields".
   *
   * @return string
   *   The label value.
   */
  protected static function missingRowsLabelFor($plural_name) {
    return sprintf('Missing %s (present in specification, absent from Drupal)', $plural_name);
  }

  /**
   * Returns an "unexpected rows" label value for a given plural name.
   *
   * @param string $plural_name
   *   The plural name for rows indicating what they signify, e.g., "bundles" or
   *   "fields".
   *
   * @return string
   *   The label value.
   */
  protected static function unexpectedRowsLabelFor($plural_name) {
    return sprintf('Unexpected %s (present in Drupal, absent from specification)', $plural_name);
  }

}
