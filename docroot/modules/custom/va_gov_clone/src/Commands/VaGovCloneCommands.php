<?php

namespace Drupal\va_gov_clone\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drupal command to Clone content.
 */
class VaGovCloneCommands extends DrushCommands {

  /**
   * The Clone Manager.
   *
   * @var \Drupal\va_gov_clone\CloneManagerInterface
   */
  protected $cloneManager;

  /**
   * Clone all content.
   *
   * @param int $office_tid
   *   ID of the office.
   *
   * @usage va-gov-clone-clone-all 123
   *   Clone the detail pages,events, staff and news nodes.
   *
   * @command va-gov-clone:clone-all
   * @aliases foo
   */
  public function cloneAll(int $office_tid) {
    $this->cloneManager->cloneAll($office_tid);
  }

}
