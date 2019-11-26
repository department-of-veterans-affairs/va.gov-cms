<?php

namespace tests\phpunit\roleChecks;

use Drupal\user\Entity\Role;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm role names.
 */
class RoleChecks extends ExistingSiteBase {

  /**
   * A test method to deterine if the role exists.
   *
   * @group security
   * @group all
   */
  public function testRole() {

    // Loads role objects.
    $role_objects = Role::loadMultiple();

    // These are the roles we want.
    $includes = [
      'anonymous',
      'authenticated',
      'content_api_consumer',
      'content_editor',
      'content_admin',
      'content_reviewer',
      'content_publisher',
      'admnistrator_users',
      'administrator',
    ];

    // Creates an array from our role objects.
    $system_roles = array_combine(array_keys($role_objects), array_map(function ($a) {
      $roles[] = $a->label();
      return $a->label();
    }, $role_objects));

    // Validates drupal roles match what we expect.
    foreach ($includes as $role) {
      $this->assertArrayHasKey($role, $system_roles);
    }

  }

  /**
   * A test method to determine User Access Admin role perms.
   */
  public function testUserAccessAdminRolePerms() {

    // Load the role.
    $admin_user = Role::load('admnistrator_users');

    $role_list = [];

    // These are the perms we want.
    $includes = [
      'assign content_api_consumer role',
      'assign content_editor role',
      'assign content_publisher role',
      'assign content_reviewer role',
      'assign selected workbench access',
    ];
    foreach ($includes as $inc) {
      if ($admin_user->hasPermission($inc)) {
        $role_list[] = $inc;
      }
    }

    // Validates role perms are there.
    foreach ($includes as $perm) {
      $this->assertContains($perm, $role_list);
    }

  }

}
