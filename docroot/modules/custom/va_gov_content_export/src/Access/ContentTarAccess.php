<?php

namespace Drupal\va_gov_content_export\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\va_gov_content_export\ContentTarLocationInterface;

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
   * The Content Tar Location Service.
   *
   * @var \Drupal\va_gov_content_export\ContentTarLocationInterface
   */
  private $contentTarLocation;

  /**
   * ContentTarAccess constructor.
   *
   * @param string $permission
   *   The permission name to check access.
   * @param \Drupal\va_gov_content_export\ContentTarLocationInterface $contentTarLocation
   *   The content tar location service.
   */
  public function __construct(string $permission, ContentTarLocationInterface $contentTarLocation) {
    $this->permission = $permission;
    $this->contentTarLocation = $contentTarLocation;
  }

  /**
   * Check access to content tar file.
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
  public function access(AccountInterface $account): AccessResultInterface {
    if (!$account->hasPermission($this->permission)) {
      return AccessResult::forbidden('Permission does not match');
    }

    $uri = $this->contentTarLocation->getUri();
    if (!$uri) {
      return AccessResult::forbidden('No config set');
    }
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
  protected function fileExists(string $uri): bool {
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

}
