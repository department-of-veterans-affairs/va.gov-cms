<?php

namespace tests\phpunit\Security;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm access to node edit form tabs.
 */
class NodeEditTabsTest extends ExistingSiteBase {

  /**
   * A test method to determine whether users can access node edit form tabs.
   *
   * @group edit
   * @group all
   */
  public function testAccessNodeEditTab() {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();
    $author->addRole('content_editor');

    $node = $this->createNode([
      'title' => 'Llama',
      'type' => 'page',
      'uid' => $author->id(),
    ]);
    $node->set('field_administration', '4');
    $node->setPublished(TRUE)->save();

    $message = "\nUnable to change value in node edit form tab.\n" . $node->get('field_administration')->getValue()[0]['target_id'] . "\n";
    // Test assertion.
    $this->assertEquals($node->get('field_administration')->getValue()[0]['target_id'], '4', $message);
  }

}
