<?php

namespace Traits;

use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;

/**
 * Provides methods to manage groups and user / group relationships.
 *
 * This trait is meant to be used only by test classes.
 */
trait GroupTrait {

  /**
   * Keep track of nodes so they can be cleaned up.
   *
   * @var \Drupal\group\Entity\Group[]
   */
  protected $groups = [];

  /**
   * Keep track of last group created.
   *
   * @var object
   */
  protected $currentGroup;

  /**
   * Creates a group.
   *
   * @param string $group_type
   *   The group type.
   * @param string $title
   *   The title of the new group.
   *
   * @return \Drupal\group\Entity\Group
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createGroup($group_type, $title) {
    $group = Group::create([
      'type' => $group_type,
      'label' => $title,
      'uid' => 1,
      'path' => ['alias' => '/test-groups/' . $this->machineNameConversion($title)],
    ]);

    $group->save();
    $this->groups[] = $group;
    $this->currentGroup = $group;
    return $group;
  }

  /**
   * Creates new user and joins user to a group.
   *
   * @param \Drupal\group\Entity\Group $group
   *   The full group entity.
   * @param string $group_role
   *   The user role within the group.
   *
   * @return \Drupal\user\Entity\User
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function newUserJoinGroup(Group $group, $group_role = NULL) {
    $user = User::create([
      'name' => "Test Group User " . rand(),
      'status' => 1,
    ]);
    $user->save();
    $this->username = $user->getAccountName();
    $this->userJoinGroup($user, $group, $group_role);

    return $user;
  }

  /**
   * Creates new user and joins user to a group.
   *
   * @param \Drupal\user\Entity\User $user
   *   The full user entity object.
   * @param \Drupal\group\Entity\Group $group
   *   The full group entity object.
   * @param string $group_role
   *   The user role within the group.
   *
   * @return \Drupal\group\Entity\Group
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function userJoinGroup(User $user, Group $group, $group_role = NULL) {
    $group->addMember($user);
    if ($group_role) {
      $membership = $group->getMember($user)->getGroupContent();
      $membership->group_roles[] = $group_role;
      $membership->save();
    }

    return $group;
  }

  /**
   * Helper function to unset current group.
   */
  public function cleanCurrentGroup() {
    if (!is_null($this->currentGroup)) {
      $this->currentGroup = new \stdClass();
    }
  }

  /**
   * Remove groups after scenario as cleanup.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function cleanGroups() {
    if (!empty($this->groups)) {
      foreach ($this->groups as $group) {
        $group->delete();
      }
    }
  }

  /**
   * Helper function to convert string to Drupal machine name.
   *
   * @param string $string
   *   String to convert.
   *
   * @return string
   *   Converted string.
   */
  private function machineNameConversion($string) {
    return preg_replace('@[^a-z0-9_]+@', '_', strtolower($string));
  }

}
