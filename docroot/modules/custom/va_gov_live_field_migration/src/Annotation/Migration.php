<?php

namespace Drupal\va_gov_live_field_migration\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation object for live field migration plugins.
 *
 * @see plugin_api
 * @see \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface
 * @see \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginManager
 *
 * @Annotation
 */
class Migration extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the migration.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
