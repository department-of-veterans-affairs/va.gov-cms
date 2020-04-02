<?php

namespace Drupal\va_gov_content_export;

use Alchemy\Zippy\FileStrategy\FileStrategyInterface;

/**
 * A Zippy Tar Strategy to use our custom tar operations.
 */
class TarStrategy implements FileStrategyInterface {

  /**
   * {@inheritDoc}
   */
  public function getAdapters() {
    return [TarAdapter::get()];
  }

  /**
   * {@inheritDoc}
   */
  public function getFileExtension() {
    return 'tar';
  }

}
