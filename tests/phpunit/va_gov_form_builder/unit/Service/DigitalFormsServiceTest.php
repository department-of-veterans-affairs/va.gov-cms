<?php

namespace tests\phpunit\va_gov_form_builder\unit\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit tests for the DigitalFormsService class.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_form_builder\Service\DigitalFormsService
 */
class DigitalFormsServiceTest extends VaGovUnitTestBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The service being tested.
   *
   * @var \Drupal\va_gov_form_builder\Service\DigitalFormsService
   */
  protected $digitalFormsService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a mock EntityTypeManagerInterface.
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);

    // Instantiate the service with the mocked dependencies.
    $this->digitalFormsService = new DigitalFormsService($this->entityTypeManager);
  }

  /**
   * Helper function to DRY up expectation setup.
   */
  private function setUpMockQuery($publishedOnly, $hasResults = TRUE) {
    // Mock the entity storage.
    $entityStorage = $this->createMock(EntityStorageInterface::class);

    // Mock the query and return node IDs.
    $query = $this->createMock(QueryInterface::class);

    $query->method('accessCheck')
      ->willReturnSelf();

    if ($publishedOnly) {
      $query->expects($this->exactly(2))
        ->method('condition')
        ->withConsecutive(
          ['type', 'digital_form'],
          ['status', 1],
        )
        ->willReturnSelf();
    }
    else {
      $query->expects($this->once())
        ->method('condition')
        ->withConsecutive(
          ['type', 'digital_form'],
        )
        ->willReturnSelf();
    }

    if ($hasResults) {
      $query->expects($this->once())
        ->method('execute')
        ->willReturn([1, 2]);
    }
    else {
      $query->expects($this->once())
        ->method('execute')
        ->willReturn(NULL);
    }

    // Mock the entity storage to return the query and nodes.
    $entityStorage->expects($this->once())
      ->method('getQuery')
      ->willReturn($query);

    if ($hasResults) {
      $entityStorage->expects($this->once())
        ->method('loadMultiple')
        ->with([1, 2])
        ->willReturn([
          1 => $this->createMock('Drupal\node\NodeInterface'),
          2 => $this->createMock('Drupal\node\NodeInterface'),
        ]);
    }

    // Mock the entity type manager.
    if ($hasResults) {
      $this->entityTypeManager->expects($this->exactly(2))
        ->method('getStorage')
        ->with('node')
        ->willReturn($entityStorage);
    }
    else {
      $this->entityTypeManager->expects($this->once())
        ->method('getStorage')
        ->with('node')
        ->willReturn($entityStorage);
    }
  }

  /**
   * Tests getDigitalForms() when $publishedOnly is TRUE.
   *
   * Query should include `status` condition.
   *
   * @covers ::getDigitalForms
   */
  public function testGetDigitalFormsPublishedOnlyTrue() {
    // We will call the method with $publishedOnly = TRUE,
    // so we set up expectations accordingly.
    $this->setUpMockQuery(TRUE);

    // Call the method, which asserts expectations set in setup.
    $this->digitalFormsService->getDigitalForms(TRUE);
  }

  /**
   * Tests getDigitalForms() when $publishedOnly is FALSE.
   *
   * Query should not include `status` condition.
   *
   * @covers ::getDigitalForms
   */
  public function testGetDigitalFormsPublishedOnlyFalse() {
    // We will call the method with $publishedOnly = FALSE,
    // so we set up expectations accordingly.
    $this->setUpMockQuery(FALSE);

    // Call the method, which asserts expectations set in setup.
    $this->digitalFormsService->getDigitalForms(FALSE);
  }

  /**
   * Tests getDigitalForms() defaults to $publishedOnly TRUE.
   *
   * @covers ::getDigitalForms
   */
  public function testGetDigitalFormsPublishedOnlyDefault() {
    // We will call the method without passing $publishedOnly,
    // and we want to ensure that the expectations are set up
    // as though the value were set to TRUE, which is the
    // expected default.
    $this->setUpMockQuery(TRUE);

    // Call the method, which asserts expectations set in setup.
    $this->digitalFormsService->getDigitalForms();
  }

  /**
   * Tests getDigitalForms() returns empty array if no results.
   *
   * @covers ::getDigitalForms
   */
  public function testGetDigitalFormsNoResults() {
    // We set up the query to return no results
    // by passing FALSE as second parameter.
    //
    // In the setup helper, this sets up expectations accordingly:
    // 1. That
    // `$this->entityTypeManager->getStorage('node')->loadMultiple($nids)`
    // will not be called, since no node ids will be returned from
    // the initial fetch.
    //
    // 2. That `$this->entityTypeManager->getStorage('node')`
    // will be called only once, rather than twice,
    // since there will be no need to call `loadMultiple`.
    $this->setUpMockQuery(TRUE, FALSE);

    // Call the method, which asserts expectations set in setup.
    $result = $this->digitalFormsService->getDigitalForms(TRUE);

    // Additionally, assert the function returns no results.
    $this->assertCount(0, $result);
  }

}
