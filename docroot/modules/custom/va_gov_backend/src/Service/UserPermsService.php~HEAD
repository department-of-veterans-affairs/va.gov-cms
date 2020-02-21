<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Access\AccessResultForbidden;
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
  private function getUser($user_id = NULL) {
    if (!empty($user_id)) {
      // Not available, so load it.
      return User::load($user_id);
    }
    else {
      return $this->currentUser;
    }
  }

  /**
   * Check which values are allowed and put into array.
   *
   * @param array $form
   *   The entity add or edit form.
   * @param array $targets
   *   The form fields to target for filtering.
   * @param int $user_id
   *   The user id.
   *
   * @return array
   *   The options that are allowed for user selection.
   */
  public function userOptionsStorage(array &$form, array $targets, $user_id) {
    $allowed_options = [];
    // Return allowed form field options by filtering disallowed items.
    foreach ($targets as $target) {

      if (in_array($target, $form) && !empty($form[$target]['widget']['#options'])) {
        $cid = 'userpermservice-dropdown-access-' . $target . '-' . $user_id;

        $cached_data = \Drupal::cache()->get($cid);
        // If we have a cache, return it.
        if (!empty($cached_data)) {
          $allowed_options = $cached_data->data;
        }
        else {
          $target_allowed_options = [];
          foreach ($form[$target]['widget']['#options'] as $header_key => $option_header) {
            if (is_array($option_header) && !empty($option_header)) {
              foreach ($option_header as $option_key => $option_item) {
                $access = $this->userAccess($option_key, 'node', $user_id);

                if ($access) {
                  $target_allowed_options[] = $option_key;
                }
              }
            }
          }
          $tags = [
            'va_gov_backend_user_perms',
            'user:' . $user_id,
          ];
          // Cache for 24 hours.
          $expire_time = time() + 24 * 60 * 60;
          \Drupal::cache()->set($cid, $target_allowed_options, $expire_time, $tags);
          $allowed_options = array_merge($target_allowed_options, $allowed_options);
        }
      }
    }

    return array_unique($allowed_options);

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
  public function userAccess($entity_id, $entity_type, $user_id, $op = NULL) {
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

    // Compare user sections against subject section to determine access.
    return array_reduce(\Drupal::entityTypeManager()->getStorage('access_scheme')->loadMultiple(), function (AccessResult $carry, AccessSchemeInterface $scheme) use ($entity, $op, $account, $results) {
      $status_class_name = get_class($scheme->getAccessScheme()->checkEntityAccess($scheme, $entity, $op, $account));
      // Return true if we have our special snowflake Outreach listing node.
      if ((count($results) > 0) && ($entity->id() === '736')) {
        return TRUE;
      }
      if ($status_class_name === AccessResultForbidden::class) {
        return FALSE;
      }
      return TRUE;
    }, AccessResult::neutral());

  }

}
