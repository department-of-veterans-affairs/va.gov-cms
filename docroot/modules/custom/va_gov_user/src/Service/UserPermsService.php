<?php

namespace Drupal\va_gov_user\Service;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\workbench_access\Entity\AccessSchemeInterface;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Database
   */
  private $database;

  /**
   * Scheme.
   *
   * @var \Drupal\workbench_access\Entity\AccessSchemeInterface
   */
  protected $scheme;

  /**
   * {@inheritDoc}
   */
  public function __construct(AccountInterface $currentUser, EntityTypeManagerInterface $entityTypeManager, Connection $database) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->database = $database;
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
   * Returns an array of user's sections.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User object.
   *
   * @return array
   *   Array of user sections where keys are term ids and values are term names.
   *   Special key 'administration' signifies user's assignment to ALL sections.
   *   NOTE: consumers of this method will have to take care of array key
   *   'administration' according to their use case.
   */
  public function getSections(AccountInterface $user) {
    $sections = [];
    $entity_storage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Get ids of sections assigned to user profile.
    $query = $this->database->select('section_association__user_id', 'sau');
    $query->join('section_association', 'sa', 'sau.entity_id = sa.id');
    $query->condition('sau.user_id_target_id', $user->id());
    $query->fields('sa', ['section_id']);
    $results = $query->execute()->fetchCol();

    if (($key = array_search('administration', $results)) !== FALSE || $user->hasPermission('bypass-workbench-access')) {
      unset($results[$key]);
      $tree = $entity_storage->loadTree('administration');
      foreach ($tree as $term) {
        $results[] = $term->tid;
      }
    }

    // Use has access only to some sections.
    // Compose list.
    $terms = $entity_storage->loadMultiple($results);
    $this->addSections($sections, $terms);

    return $sections;
  }

  /**
   * Add parent and child sections to getSections() results.
   *
   * @param array $sections
   *   Array of section tids/names.
   * @param array $terms
   *   Taxonomy terms.
   */
  protected function addSections(array &$sections, array $terms) {
    $entity_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    foreach ($terms as $term) {
      $sections[$term->id()] = [
        'name' => $term->getName(),
        'hasChildren' => empty($entity_storage->loadChildren($term->id())) ? FALSE : TRUE,
      ];
      $this->addSections($sections, $entity_storage->loadChildren($term->id()));
    }
  }

  /**
   * Returns the section and parents to which an entity belongs.
   *
   * @param int $tid
   *   The term id for the lookup.
   *
   * @return array
   *   The ancestor terms going up the tree.
   */
  public function entitySections($tid) {
    $ancestors = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($tid);
    $list = [];
    foreach ($ancestors as $term) {
      $list[$term->id()] = $term->label();
    }
    return $list;
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

        if ($target === 'field_listing') {
          // Add form id to cid for field_listing, since lists for various
          // content type are different.
          $cid = 'userpermservice-dropdown-access-' . $form['#form_id'] . '-' . $target . '-' . $user_id;
        }

        $cached_data = \Drupal::cache()->get($cid);
        // If we have a cache, return it.
        if (!empty($cached_data)) {
          $allowed_options = $cached_data->data;
        }
        else {
          $target_allowed_options = [];
          $account = $this->getUser($user_id);

          foreach ($form[$target]['widget']['#options'] as $header_key => $option_header) {
            if (!is_array($option_header) && !empty($option_header)) {
              // Exception for one level select fields that don't
              // have opt groups.
              $access = $this->userAccess($header_key, 'node', $account, $target);
              if ($access) {
                $target_allowed_options[] = $header_key;
              }
            }
            elseif (is_array($option_header) && !empty($option_header)) {
              foreach ($option_header as $option_key => $option_item) {
                $access = $this->userAccess($option_key, 'node', $account, $target);

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
   * @param \Drupal\core\Session\AccountInterface $account
   *   The user id.
   * @param string $target
   *   The target field name.
   * @param string $op
   *   E.g., create, update, delete.
   *
   * @return bool
   *   TRUE if the user has access, FALSE otherwise.
   */
  public function userAccess($entity_id, $entity_type, AccountInterface $account, $target, $op = NULL) {
    // When using entity reference view as a source for field options,
    // first option has key '_none' and should be ignored in access checks.
    if ($entity_id === '_none') {
      return TRUE;
    }

    $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);

    // When working with field_listing, we need to fetch an entity from a
    // parent entity's field_office.
    if ($target === 'field_listing') {
      $field_office_target_id = $entity->get('field_office')->target_id;
      $entity = $this->entityTypeManager->getStorage($entity_type)->load($field_office_target_id);
    }

    // We want create access perm for most cases. For occasional snowflake
    // situations, we may allow a different permission: e.g., 'view' for
    // some listing types.
    if (empty($op)) {
      $op = 'create';
    }

    // Compare user sections against subject section to determine access.
    return array_reduce($this->entityTypeManager->getStorage('access_scheme')->loadMultiple(), function (AccessResult $carry, AccessSchemeInterface $scheme) use ($entity, $op, $account) {
      $status_class_name = get_class($scheme->getAccessScheme()->checkEntityAccess($scheme, $entity, $op, $account));
      if ($status_class_name === AccessResultForbidden::class) {
        return FALSE;
      }
      return TRUE;
    }, AccessResult::neutral());
  }

}
