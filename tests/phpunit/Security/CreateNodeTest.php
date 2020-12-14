<?php

namespace tests\phpunit\Security;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm node creation permissions.
 */
class CreateNodeTest extends ExistingSiteBase {

  /**
   * A test method to deterine the amount of time it takes to create a node.
   *
   * @group security
   * @group all
   *
   * @dataProvider getRoles
   */
  public function testCreateNodePermissions($role) {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();
    // We cannot assign the anonymous role to skip role assignment.
    if ($role != 'authenticated') {
      $author->addRole($role);
    }
    $author->save();

    $node = $this->createNode([
      'title' => 'Llama',
      'type' => 'page',
      'uid' => $author->id(),
    ]);
    $node->setPublished()->save();

    // As a user without permission to create a page node I cannot create nodes.
    $this->assertNotNull($node, 'Users with restricted role' . $role . ' are able to create nodes.');
  }

  /**
   * Returns benchmark time to beat in order for test to succeed.
   *
   * @return array
   *   Array containing entity type as string and expected count as int
   */
  public function getRoles() {
    return [
      ['authenticated'],
      ['content_api_consumer'],
      ['admnistrator_users'],
    ];
  }

}
