<?php

namespace Drupal\va_gov_content_export\Controller;

use Drupal\va_gov_content_export\ContentTarLocationInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Page controller to export a content tar.
 *
 * @package Drupal\va_gov_content_export\Controller
 */
class ContentTar extends ControllerBase {

  /**
   * The Content Tar Location Service.
   *
   * @var \Drupal\va_gov_content_export\ContentTarLocationInterface
   */
  protected $contentTarLocation;

  /**
   * ContentTar constructor.
   *
   * @param \Drupal\va_gov_content_export\ContentTarLocationInterface $contentTarLocation
   *   The content tar location service.
   */
  public function __construct(ContentTarLocationInterface $contentTarLocation) {
    $this->contentTarLocation = $contentTarLocation;
  }

  /**
   * Export the tar file.
   *
   * The main purpose of this page callback is to serve up the content.tar
   * if it exists.  If the file does not exist then return a 404.
   */
  public function exportTar() {
    // At this point we know we have a file because there is an access check
    // in Drupal\va_gov_content_export\Access\ContentTarAccess which is
    // called before we get to this point in the code.
    $url = $this->contentTarLocation->getUrl();
    return new RedirectResponse($url);
  }

}
