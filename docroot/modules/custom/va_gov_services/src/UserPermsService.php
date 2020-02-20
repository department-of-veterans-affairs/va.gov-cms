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
   * Return an access status string for entity.
   *
   * @param string $entity_id
   *   The entity id.
   * @param string $entity_type
   *   E.g. node, block.
   * @param string $user_id
   *   The user id.
   * @param string $op
   *   E.g., create, update, delete.
   *
   * @return string
   *   Access will be TRUE or FALSE.
   */
  public function userAccess($entity_id, $entity_type, $user_id = NULL, $op = NULL) {

    $account = $this->currentUser;
    // If uid passed, use it.
    if (!empty($user_id)) {
      $account = User::load($user_id);
    }
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);

    // If op is passed, use it.
    if (empty($op)) {
      $op = 'create';
      if (strpos($entity->bundle(), '_listing') !== FALSE) {
        $op = 'view';
      }
    }
    return array_reduce(\Drupal::entityTypeManager()->getStorage('access_scheme')->loadMultiple(), function (AccessResult $carry, AccessSchemeInterface $scheme) use ($entity, $op, $account) {
      $status_class_name = get_class($scheme->getAccessScheme()->checkEntityAccess($scheme, $entity, $op, $account));
      if ($status_class_name === 'Drupal\Core\Access\AccessResultForbidden') {
        return FALSE;
      }
      return TRUE;
    }, AccessResult::neutral());

  }

}
