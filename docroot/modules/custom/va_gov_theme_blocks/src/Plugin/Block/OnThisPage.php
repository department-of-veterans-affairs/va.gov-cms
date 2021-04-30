<?php

namespace Drupal\va_gov_theme_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'OnThisPage' block.
 *
 * @Block(
 *  id = "on_this_page",
 *  admin_label = @Translation("On this page"),
 * )
 */
class OnThisPage extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'on_this_page';
    $build['on_this_page']['#markup'] = 'Implement OnThisPage.';

    return $build;
  }

}
