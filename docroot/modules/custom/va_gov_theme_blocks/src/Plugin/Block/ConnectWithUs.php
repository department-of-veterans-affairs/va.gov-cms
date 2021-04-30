<?php

namespace Drupal\va_gov_theme_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ConnectWithUs' block.
 *
 * @Block(
 *  id = "connect_with_us",
 *  admin_label = @Translation("Connect with us"),
 * )
 */
class ConnectWithUs extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'connect_with_us';
    $build['connect_with_us']['#markup'] = 'Implement ConnectWithUs.';

    return $build;
  }

}
