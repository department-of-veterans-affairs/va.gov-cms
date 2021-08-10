<?php

namespace Drupal\va_gov_clone\Commands;

use Drupal\va_gov_clone\CloneManagerInterface;
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
   * Constructor.
   *
   * @param \Drupal\va_gov_clone\CloneManagerInterface $cloneManager
   *   The Clone Manager Class.
   */
  public function __construct(CloneManagerInterface $cloneManager) {
    parent::__construct();
    $this->cloneManager = $cloneManager;
  }

  /**
   * Clone all content.
   *
   * @param int $office_tid
   *   ID of the office.
   *
   * @usage va-gov-clone:clone-all 123
   *   Clone the detail pages,events, staff and news nodes.
   *
   * @command va-gov-clone:clone-all
   * @aliases foo
   */
  public function cloneAll(int $office_tid) {
    $count = $this->cloneManager->cloneSection($office_tid);
    $this->io()->success("$count entities cloned.");
  }

}
