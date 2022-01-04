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
   * {@inheritDoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation !== 'view') {
      return parent::checkAccess($entity, $operation, $account);
    }
    // Allow read access for MLC entities that are internal but unrouted.
    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $entity */
    $urlObject = $entity->getUrlObject();
    if (
      $account->hasPermission('va gov graphql read menu content') &&
      $operation === 'view' &&
      !$urlObject->isExternal() &&
      !$urlObject->isRouted() &&
      $entity->isEnabled()
    ) {
      return AccessResult::allowed()
        ->cachePerPermissions()
        ->addCacheableDependency($entity);
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
