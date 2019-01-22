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
   */
  public function testRole() {

    // Loads role objects.
    $role_objects = Role::loadMultiple();

    // These are the roles we want.
    $includes = [
      'anonymous',
      'authenticated',
      'content_editor',
      'content_reviewer',
      'content_publisher',
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

}
