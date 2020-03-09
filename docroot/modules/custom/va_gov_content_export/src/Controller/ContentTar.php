<?php

namespace Drupal\va_gov_content_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Page controller to export a content tar.
 *
 * @package Drupal\va_gov_content_export\Controller
 */
class ContentTar extends ControllerBase {

  /**
   * Export the tar file.
   *
   * The main purpose of this page callback is to serve up the content.tar
   * if it exists.  If the file does not exist then return a 404.
   */
  public function exportTar() {
    $url = $this->getContentTarUrl();
    $url->setAbsolute(TRUE);
    return new RedirectResponse($url->toString());
  }

  /**
   * Get the path to the content.tar.
   *
   * @return \Drupal\Core\Url|null
   *   The relative path tot eh file.
   */
  protected function getContentTarUrl() : ?Url {
    $path_to_content_tar = $this->config('va_gov.content_export.content_tar_path');
    return Url::fromUri($path_to_content_tar->get());
  }

}
