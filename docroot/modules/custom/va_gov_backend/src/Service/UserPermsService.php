<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * The entity interface.
   *
   * @var object
   *  The entity interface object.
   */
  private $entityInterface;

  /**
   * UserPermsService constructor.
   */
  public function __construct(AccountInterface $currentUser, EntityTypeManagerInterface $entityInterface) {
    $this->currentUser = $currentUser;
    $this->entityInterface = $entityInterface;
  }

  /**
   * Getter setter for a, user, defaults to current user.
   *
   * @param int $user_id
   *   A user id to lookup the user. (optional)
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The user specified by $user_id or the current user
   */
  private  function getUser($user_id = NULL) {
    if (!empty($user_id)) {
      // Not available, so load it.
      return User::load($user_id);
    }
    else {
      return $this->currentUser;
    }
  }

  /**
   * Check to see if a user has access.
   *
   * @param string $entity_id
   *   The entity id.
   * @param string $entity_type
   *   E.g. node, block.
   * @param int $user_id
   *   The user id.
   * @param string $op
   *   E.g., create, update, delete.
   *
   * @return bool
   *   TRUE if the user has access, FALSE otherwise.
   */
  public function userAccess($entity_id, $entity_type, $user_id = NULL, $op = NULL) {

    $account = $this->getUser($user_id);

    $entity = $this->entityInterface->getStorage($entity_type)->load($entity_id);

    // We want create access perm for most cases. For occasional snowflake
    // situations, we may allow a different permission: e.g., 'view' for
    // some listing types.
    if (empty($op)) {
      $op = 'create';
    }

    // Special snowflake check for Outreach section - unique perms set beyond
    // scope of workbench_access.
    $database = \Drupal::database();
    $query = $database->select('section_association__user_id', 's');
    $query->condition('s.user_id_target_id', $account->id());
    $query->condition('s.entity_id', 4);
    $query->fields('s', ['entity_id']);
    $results = $query->execute()->fetchAll();
    if (count($results) > 0) {
      return TRUE;
    }

    // Compare user sections against subject section to determine access.
    return array_reduce(\Drupal::entityTypeManager()->getStorage('access_scheme')->loadMultiple(), function (AccessResult $carry, AccessSchemeInterface $scheme) use ($entity, $op, $account) {
      $status_class_name = get_class($scheme->getAccessScheme()->checkEntityAccess($scheme, $entity, $op, $account));
      if ($status_class_name === 'Drupal\Core\Access\AccessResultForbidden') {
        return FALSE;
      }
      return TRUE;
    }, AccessResult::neutral());

  }

}
