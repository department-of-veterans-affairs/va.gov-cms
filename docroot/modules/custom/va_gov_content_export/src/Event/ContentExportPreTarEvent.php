<?php

namespace Drupal\va_gov_content_export\Event;

use Drupal\va_gov_content_export\Archive\ArchiveArgs;
use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a node insertion demo event for event listeners.
 */
class ContentExportPreTarEvent extends Event {

  const CONTENT_EXPORT_PRE_TAR_EVENT = 'va_gov_content_export.pre.tar';

  /**
   * Archive Args.
   *
   * @var \Drupal\va_gov_content_export\Archive\ArchiveArgs
   */
  protected $archiveArgs;

  /**
   * Constructs a va_gov_content_export event object.
   *
   * @param \Drupal\va_gov_content_export\Archive\ArchiveArgs $archiveArgs
   *   The Archive Args.
   */
  public function __construct(ArchiveArgs $archiveArgs) {
    $this->archiveArgs = $archiveArgs;
  }

  /**
   * Getter for tarPath.
   *
   * @return \Drupal\va_gov_content_export\Archive\ArchiveArgs
   *   Archive Arguments.
   */
  public function getArchieArgs() {
    return $this->archiveArgs;
  }

}
