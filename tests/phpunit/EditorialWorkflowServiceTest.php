<?php

namespace tests\phpunit;

use Drupal\va_gov_workflow_assignments\Service\EditorialWorkflow;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test the EditorialWorkflow service.
 */
class EditorialWorkflowServiceTest extends ExistingSiteBase {

  /**
   * The tested EditorialWorkflow service.
   *
   * @var \Drupal\va_gov_workflow_assignments\Service\EditorialWorkflow|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $editorialWorkflow;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->editorialWorkflow = new EditorialWorkflow(\Drupal::database());
  }

  /**
   * Verify getLatestArchivedRevisionId method.
   *
   * @group functional
   * @group all
   */
  public function testGetLatestArchivedRevisionId() {
    $author = $this->createUser();
    $node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $node->setPublished()->save();
    $this->assertEquals(0, $this->editorialWorkflow->getLatestArchivedRevisionId($node));

    $node->set('moderation_state', 'archived')->save();
    $this->assertEquals($node->getRevisionId(), $this->editorialWorkflow->getLatestArchivedRevisionId($node));
  }

  /**
   * Verify getLatestPublishedRevisionId method.
   *
   * @group functional
   * @group all
   */
  public function testGetLatestPublishedRevisionId() {
    $author = $this->createUser();
    $node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $node->save();
    $this->assertEquals(0, $this->editorialWorkflow->getLatestPublishedRevisionId($node));

    $node->set('moderation_state', 'published')->save();
    $revision_id = $node->getRevisionId();
    $this->assertEquals($revision_id, $this->editorialWorkflow->getLatestPublishedRevisionId($node));

    $node->set('moderation_state', 'archived')->save();
    $this->assertEquals($revision_id, $this->editorialWorkflow->getLatestPublishedRevisionId($node));
  }

}
