<?php

namespace Drupal\va_gov_build_trigger\Environment\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation object for environment plugins.
 *
 * @see plugin_api
 * @see \Drupal\va_gov_build_trigger\Environment\EnvironmentInterface
 * @see \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
 *
 * @Annotation
 */
class Environment extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the environemnt.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
