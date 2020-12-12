<?php

namespace tests\phpunit\Service;

use Drupal\va_gov_bulk\Service\ModerationActions;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test the ModerationActions service.
 */
class ModerationActionsServiceTest extends ExistingSiteBase {

  /**
   * The tested ModerationActions service.
   *
   * @var \Drupal\va_gov_bulk\Service\ModerationActions|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moderationActions;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->moderationActions = new ModerationActions(\Drupal::currentUser(), \Drupal::time(), \Drupal::entityTypeManager());
  }

  /**
   * Verify archiveNode method.
   *
   * @group functional
   * @group all
   */
  public function testArchiveNode() {
    $author = $this->createUser();
    $node = $this->createNode([
      'title' => 'Test Office',
      'type' => 'office',
      'uid' => $author->id(),
    ]);
    $node->set('moderation_state', 'published');
    $node->setRevisionCreationTime(\Drupal::time()->getRequestTime());
    $node->setRevisionLogMessage('Test publish revision');
    $node->setRevisionUserId($author->id());
    $node->save();
    $this->assertEquals('published', $node->moderation_state->value);

    $this->moderationActions->archiveNode($node);
    $this->assertEquals('archived', $node->moderation_state->value);
  }

  /**
   * Verify publishLatestRevision method.
   *
   * @group functional
   * @group all
   */
  public function testPublishLatestRevision() {
    $author = $this->createUser();
    $node = $this->createNode([
      'title' => 'Test Office',
      'type' => 'office',
      'uid' => $author->id(),
    ]);
    $node->set('moderation_state', 'published');
    $node->setRevisionCreationTime(\Drupal::time()->getRequestTime());
    $node->setRevisionLogMessage('Test publish revision');
    $node->setRevisionUserId($author->id());
    $node->save();
    $this->assertEquals('published', $node->moderation_state->value);

    $node->setTitle('Test Office UPDATED');
    $node->set('moderation_state', 'draft');
    $node->setRevisionCreationTime(\Drupal::time()->getRequestTime());
    $node->setRevisionLogMessage('Test publish revision');
    $node->setRevisionUserId($author->id());
    $node->save();
    $this->assertEquals('draft', $node->moderation_state->value);

    $current_revision = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals('Test Office', $current_revision->getTitle());
    $this->assertEquals('published', $current_revision->moderation_state->value);

    $this->moderationActions->publishLatestRevision($current_revision);

    $current_revision = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertEquals('Test Office UPDATED', $current_revision->getTitle());
    $this->assertEquals('published', $current_revision->moderation_state->value);
  }

}
