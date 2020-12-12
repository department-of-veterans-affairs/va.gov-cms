<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\va_gov_backend\Deploy\FileOperationsTrait;
use Drupal\va_gov_content_export\Archive\ArchiveArgs;
use Drupal\va_gov_content_export\Archive\ArchiveArgsFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * CMS Export plugin.
 */
class CMSExport implements DeployPluginInterface {
  use FileOperationsTrait;

  /**
   * A stream wrapper.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperInterface
   */
  protected $streamWrapperInstance;

  /**
   * {@inheritDoc}
   */
  public function match(Request $request): bool {
    $current_path = $request->getPathInfo();
    if ($current_path === '/cms-export/content') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function run(Request $request, string $app_root, string $site_path) {
    $this->registerStreamWrapper();
    try {
      if ($this->fileExists()) {
        // The cms export tar file will exist if either
        // a previous call created the file
        // or the drush job va-gov-cms-export-generate-tar was call.
        // The va-gov-cms-export-generate-tar drush job is called during deploy.
        $url = $this->getUrl();

        return RedirectResponse::create($url);
      }
    }
    catch (ContainerNotInitializedException $e) {
      // This error can occur if the file doesn't exist.
    }
  }

  /**
   * Get the Arguments used to find the tar archive.
   *
   * @return \Drupal\va_gov_content_export\Archive\ArchiveArgs
   *   The arguments used to find the tar archive.
   */
  protected function getArchiveArgs() : ArchiveArgs {
    $archiveArgsFactory = new ArchiveArgsFactory();
    return $archiveArgsFactory->createContentArgs();
  }

  /**
   * {@inheritDoc}
   */
  protected function getStreamWrapper() : ?StreamWrapperInterface {
    return $this->streamWrapperInstance;
  }

  /**
   * {@inheritDoc}
   */
  protected function setStreamWrapper(StreamWrapperInterface $streamWrapper) {
    $this->streamWrapperInstance = $streamWrapper;
  }

  /**
   * {@inheritDoc}
   */
  protected function getOutputUri(): string {
    $archiveArgs = $this->getArchiveArgs();
    return $archiveArgs->getOutputPath();
  }

}
