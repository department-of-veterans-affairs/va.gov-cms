<?php

namespace Drupal\va_gov_services;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\workbench_access\Entity\AccessSchemeInterface;
use Drupal\Core\Access\AccessResult;

/**
 * For exporting User access status to service.
 */
class UserPermsService {

  /**
   * The active user.
   *
   * @var object
   *  The user object.
   */
  private $currentUser;

  /**
   * UserPermsService constructor.
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * Returns a Drupal user ID.
   */
  public function userId() {
    return $this->currentUser->id();
  }

  /**
   * Returns a Drupal username.
   */
  public function userName() {
    return $this->currentUser->getDisplayName();
  }

  /**
   * Returns a Drupal user's roles.
   */
  public function userRoles() {
    return $this->currentUser->getRoles();
  }

  /**
   * Returns true if user has perm.
   */
  public function userPerm(String $permString) {
    return $this->currentUser->hasPermission($permString);
  }

  /**
   * Return an access status object for entity.
   *
   * @param string $user_id
   *   The user id.
   * @param string $entity_id
   *   The entity id.
   * @param string $entity_type
   *   E.g. node, block.
   * @param string $op
   *   E.g., create, update, delete.
   *
   * @return object
   *   Object indicating Neutral (allowed) or Forbidden (disallowed) status.
   */
  public function userAccess($user_id, $entity_id, $entity_type, $op) {

    $account = User::load($user_id);
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
    return array_reduce(\Drupal::entityTypeManager()->getStorage('access_scheme')->loadMultiple(), function (AccessResult $carry, AccessSchemeInterface $scheme) use ($entity, $op, $account) {
      $carry->addCacheableDependency($scheme)->cachePerPermissions()->addCacheableDependency($entity);
      return $carry->orIf($scheme->getAccessScheme()->checkEntityAccess($scheme, $entity, $op, $account));
    }, AccessResult::neutral());

  }

}
