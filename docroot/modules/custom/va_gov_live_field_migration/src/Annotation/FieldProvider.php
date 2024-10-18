<?php

namespace Drupal\va_gov_live_field_migration\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation object for field provider plugins.
 *
 * @see plugin_api
 * @see \Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginInterface
 * @see \Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginManager
 *
 * @Annotation
 */
class FieldProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the field provider plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
