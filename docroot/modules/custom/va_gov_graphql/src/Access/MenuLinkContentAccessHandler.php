<?php

namespace Drupal\va_gov_graphql\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\menu_link_content\MenuLinkContentAccessControlHandler;

/**
 * Allow menu links to be viewed if the path doesn't exist in Drupal.
 */
class MenuLinkContentAccessHandler extends MenuLinkContentAccessControlHandler {

  /**
   * Paths which are allowed to have view access.
   *
   * @return string[]
   *   Paths we want to allow access for.
   */
  protected function getAllowedPaths() : array {
    return [
      '/find-locations',
      '/health-care/apply/application',
    ];
  }

  /**
   * {@inheritDoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation !== 'view') {
      return parent::checkAccess($entity, $operation, $account);
    }

    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $entity */
    if ($operation === 'view' &&
      in_array($entity->getUrlObject()->toString(), $this->getAllowedPaths())) {

      return AccessResult::allowed()
        ->cachePerPermissions()
        ->addCacheableDependency($entity);
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
