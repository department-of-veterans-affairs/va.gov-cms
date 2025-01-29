<?php

namespace Drupal\va_gov_eca\Plugin\views\display;

use Drupal\views\Plugin\views\display\DefaultDisplay;

/**
 * A Views display plugin for ECA.
 *
 * "eca_views_display" is a custom property, used with
 * \Drupal\views\Views::getApplicableViews() to retrieve all views with an
 * 'ECA Result' display.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "eca_result",
 *   title = @Translation("ECA Result"),
 *   help = @Translation("Views diplay plugin for ECA."),
 *   theme = "views_view",
 *   register_theme = FALSE,
 *   uses_menu_links = FALSE,
 *   eca_views_display = TRUE
 * )
 */
class EcaViewsResultDisplay extends DefaultDisplay {

  /**
   * {@inheritdoc}
   */
  protected $usesAJAX = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $usesPager = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $usesAttachments = FALSE;

  /**
   * {@inheritdoc}
   */
  public function usesExposed() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Force the style plugin to 'default' and the row plugin to
    // 'fields'.
    $options['style']['contains']['type'] = ['default' => 'default'];
    $options['defaults']['default']['style'] = FALSE;
    $options['row']['contains']['type'] = ['default' => 'fields'];
    $options['defaults']['default']['row'] = FALSE;

    // Set the display title to an empty string (not used in this display type).
    $options['title']['default'] = '';
    $options['defaults']['default']['title'] = FALSE;

    return $options;
  }

}
