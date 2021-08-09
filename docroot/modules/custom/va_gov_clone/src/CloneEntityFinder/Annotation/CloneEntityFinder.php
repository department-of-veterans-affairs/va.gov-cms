<?php

namespace Drupal\va_gov_clone\CloneEntityFinder\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Clone handler item annotation object.
 *
 * @see \Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderDiscovery
 * @see \Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderInterface
 * @see plugin_api
 *
 * @Annotation
 */
class CloneEntityFinder extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
