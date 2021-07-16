<?php

namespace tests\phpunit\Security;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm admin dashboard security.
 */
class AdminDashboardTest extends ExistingSiteBase {

  /**
   * A test method to check permissions to access the admin dashboard.
   *
   * @group security
   * @group all
   *
   * @dataProvider getRoles
   */
  public function testAdminPermissions($role) {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();
    // We cannot assign the anonymous role to skip role assignment.
    if ($role != 'authenticated') {
      $author->addRole($role);
    }
    $author->save();

    $name = $author->getAccountName();
    $account = user_load_by_name($name);
    $uid = $account->id();

    $url = 'http://localhost/user/' . $uid . '/moderation/dashboard';

    $this->setCurrentUser($account);
    $this->visit($url);
    $statuscode = $this->getSession()->getStatusCode();

    // As a user without permission I cannot access the admin dashboard.
    $this->assertNotEquals($statuscode, '443', 'Users with restricted role ' . $role . 'are able to access the dashboard.');
  }

  /**
   * Returns roles to test against permissions.
   *
   * @return array
   *   Array containing roles as  a string
   */
  public function getRoles() {
    return [
      ['authenticated'],
      ['content_api_consumer'],
      ['redirect_admnistrator'],
    ];
  }

}
