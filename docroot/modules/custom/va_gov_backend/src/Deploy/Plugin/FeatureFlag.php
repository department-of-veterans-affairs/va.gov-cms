<?php

namespace Drupal\va_gov_backend\Deploy\Plugin;

use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\va_gov_backend\Deploy\FileOperationsTrait;
use Drupal\va_gov_flags\Export\ExportFeature;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Deploy Plugin for feature flags.
 */
class FeatureFlag implements DeployPluginInterface {
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
    if ($current_path === '/flags_list') {
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
        $file_content = $this->readFile();
        return JsonResponse::fromJsonString($file_content);
      }
    }
    catch (ContainerNotInitializedException $e) {
      // This error can occur if the file doesn't exist.
    }
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
  protected function getOutputUri() : string {
    return ExportFeature::getPath();
  }

}
