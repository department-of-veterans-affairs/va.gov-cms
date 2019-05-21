<?php

namespace tests\phpunit;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm access to node edit form tabs.
 */
class AccessNodeEditTabs extends ExistingSiteBase {

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
      'uid' => $author,
    ]);

    $node->moderation_state->value = 'review';
    $node->save();

    $message = "\nUnable to access workflow moderation and change value in node edit form \n";

    // Test assertion.
    $this->assertEquals($node->get('moderation_state')->getValue()[0]['value'], 'review', $message);

  }

}
