<?php

namespace tests\phpunit\Service;

use Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepository;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Test the EditorialWorkflowContentRepository service.
 *
 * @group functional
 * @group all
 */
class EditorialWorkflowContentRepositoryServiceTest extends VaGovExistingSiteBase {

  /**
   * The tested EditorialWorkflowContentRepository service.
   *
   * @var \Drupal\va_gov_workflow_assignments\Service\EditorialWorkflowContentRepository|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $editorialWorkflowContentRepository;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();
    $this->editorialWorkflowContentRepository = new EditorialWorkflowContentRepository(\Drupal::database());
  }

  /**
   * Verify getLatestArchivedRevisionId method.
   */
  public function testGetLatestArchivedRevisionId() {
    $author = $this->createUser();
    $node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $node->setPublished()->save();
    $this->assertEquals(0, $this->editorialWorkflowContentRepository->getLatestArchivedRevisionId($node));

    $node->set('moderation_state', 'archived')->save();
    $this->assertEquals($node->getRevisionId(), $this->editorialWorkflowContentRepository->getLatestArchivedRevisionId($node));
  }

  /**
   * Verify getLatestPublishedRevisionId method.
   */
  public function testGetLatestPublishedRevisionId() {
    $author = $this->createUser();
    $node = $this->createNode([
      'title' => 'VA Test health care',
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);
    $node->save();
    $this->assertEquals(0, $this->editorialWorkflowContentRepository->getLatestPublishedRevisionId($node));

    $node->set('moderation_state', 'published')->save();
    $revision_id = $node->getRevisionId();
    $this->assertEquals($revision_id, $this->editorialWorkflowContentRepository->getLatestPublishedRevisionId($node));

    $node->set('moderation_state', 'archived')->save();
    $this->assertEquals($revision_id, $this->editorialWorkflowContentRepository->getLatestPublishedRevisionId($node));
  }

}
