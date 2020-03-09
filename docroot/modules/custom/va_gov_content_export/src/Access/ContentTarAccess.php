<?php

namespace Drupal\va_gov_content_export\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Control Access to the content export file.
 */
class ContentTarAccess implements AccessInterface {

  /**
   * The name of the permission to check access.
   *
   * @var string
   */
  protected $permission;

  /**
   * The config name which holds the uri to the file.
   *
   * @var string
   */
  protected $contentFileConfig;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ContentTarAccess constructor.
   *
   * @param string $permission
   *   The permission name to check access.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Access to the Config.
   */
  public function __construct(string $permission, ConfigFactoryInterface $configFactory) {
    $this->permission = $permission;
    $this->configFactory = $configFactory;
  }

  /**
   * Check access sto content tar file.
   *
   * Access is determined on the following:
   * 1. Does the user have access to the permission being injected
   * 2. Does the config exist
   * 3. Does the file exist.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The an account object.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) : AccessResultInterface {
    if (!$account->hasPermission($this->permission)) {
      return AccessResult::forbidden('Permission does not match');
    }

    $uri = $this->getContentTarUrl();
    if (!$this->fileExists($uri)) {
      return AccessResult::forbidden('file does not exist');
    }

    return AccessResult::allowed();
  }

  /**
   * Check if the file uri exists.
   *
   * @param string $uri
   *   The file URI including the schemea.
   *
   * @return bool
   *   IF the file exists.
   */
  protected function fileExists(string $uri) : bool {
    try {
      if (file_exists($uri)) {
        return TRUE;
      }

      return FALSE;
    }
    catch (FileExistsException $e) {
      return FALSE;
    }
  }

  /**
   * Get the content url.
   *
   * @return string|null
   *   The uri to the content tar file.
   */
  protected function getContentTarUrl() : ?string {
    $path_to_tar_config = $this->configFactory->get($this->contentFileConfig);
    if (!$path_to_tar_config->get()) {
      return NULL;
    }

    return $path_to_tar_config->get();
  }

}
