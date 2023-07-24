<?php

namespace Drupal\va_gov_content_release\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation object for content release strategy plugins.
 *
 * @see plugin_api
 * @see \Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginInterface
 * @see \Drupal\va_gov_content_release\Strategy\Plugin\StrategyPluginManager
 *
 * @Annotation
 */
class ContentReleaseStrategy extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the strategy.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
