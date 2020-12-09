<?php

namespace tests\phpunit\Security;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm access to workflow moderation.
 */
class WorkflowTest extends ExistingSiteBase {

  /**
   * A test method to determine whether users can access workflow moderation.
   *
   * @group edit
   * @group all
   */
  public function testAccessWorkflow() {

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
