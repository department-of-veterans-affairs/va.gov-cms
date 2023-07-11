<?php

namespace Drupal\va_gov_content_release\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the annotation object for entity event strategy plugins.
 *
 * @see plugin_api
 * @see \Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginInterface
 * @see \Drupal\va_gov_content_release\EntityEvent\Strategy\Plugin\StrategyPluginManager
 *
 * @Annotation
 */
class EntityEventStrategy extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the entity event strategy.
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
