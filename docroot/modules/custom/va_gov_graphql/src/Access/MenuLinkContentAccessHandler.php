<?php

namespace Drupal\va_gov_graphql\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\menu_link_content\MenuLinkContentAccessControlHandler;

/**
 * An access handler to allow menu links to be viewed if the path doesn't exist
 * in Drupal.
 */
class MenuLinkContentAccessHandler extends MenuLinkContentAccessControlHandler {

  /**
   * Paths which are allowed to have view access.
   *
   * @return string[]
   */
  protected function getAllowedPaths() : array {
    return [
      '/find-locations',
      '/health-care/apply/application'
    ];
  }

  /**
   * {@inheritDoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation !== 'view') {
      return parent::checkAccess($entity, $operation, $account);
    }

    if ($operation !== 'view' &&
      in_array($entity->getUrlObject()->toString(), $this->getAllowedPaths())) {

      return AccessResult::allowed()
        ->cachePerPermissions()
        ->addCacheableDependency($entity);
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
