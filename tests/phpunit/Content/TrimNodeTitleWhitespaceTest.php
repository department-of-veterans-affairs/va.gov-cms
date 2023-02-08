<?php

namespace tests\phpunit\Content;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * A test to confirm the proper functioning of TrimNodeTitleWhitespace.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_backend\EventSubscriber\EntityEventSubscriber
 */
class TrimNodeTitleWhitespaceTest extends VaGovExistingSiteBase {

  /**
   * Confirm that the trimmed title is as expected.
   *
   * @param string $titleInput
   *   The node title to test.
   * @param string $expectedTitleOutput
   *   The expected node title.
   *
   * @dataProvider confirmExpectedOutputDataProvider
   */
  public function testConfirmExpectedOutput($titleInput, $expectedTitleOutput) {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();

    // Create a node with the input title.
    $entity = $this->createNode([
      'title' => $titleInput,
      'type' => 'health_care_region_page',
      'uid' => $author->id(),
    ]);

    $this->assertEquals($expectedTitleOutput, $entity->getTitle());
  }

  /**
   * Confirm that 'title' trimming is limited to nodes.
   *
   * @param string $entityTypeId
   *   The type of entity to create.
   * @param string $titleInput
   *   The entity title to input.
   * @param string $expectedTitleOutput
   *   The expected entity title.
   *
   * @dataProvider titleTrimRestrictedToNodesDataProvider
   */
  public function testTitleTrimRestrictedToNodes($entityTypeId, $titleInput, $expectedTitleOutput) {
    $this->markTestSkipped('this test is encountering repeated failures. will be re-enabled in #11964.');
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();
    // Create a vocabulary for testing terms.
    $vocabulary = $this->createVocabulary();

    switch ($entityTypeId) {
      case 'node':
        // Create a node with the input title.
        $entity = $this->createNode([
          'title' => $titleInput,
          'type' => 'health_care_region_page',
          'uid' => $author->id(),
        ]);
        $actualOutput = $entity->getTitle();
        break;

      case 'term':
        $term = $this->createTerm($vocabulary, ['name' => $titleInput]);
        $actualOutput = $term->getName();
        break;

      case 'user':
        $user = $this->createUser([], $titleInput);
        $actualOutput = $user->getAccountName();
        break;

      default:
        $actualOutput = 'bad entity type';
    }

    $this->assertEquals($expectedTitleOutput, $actualOutput);
  }

  /**
   * Data provider for testConfirmExpectedOutput.
   */
  public function confirmExpectedOutputDataProvider() {
    return [
      [
        'This has no spaces',
        'This has no spaces',
      ],
      [
        'This had trailing spaces    ',
        'This had trailing spaces',
      ],
      [
        '    This had leading spaces',
        'This had leading spaces',
      ],
      [
        '     This was all spaced out       ',
        'This was all spaced out',
      ],
      [
        '     Interior     spaces     are not     touched       ',
        'Interior     spaces     are not     touched',
      ],
    ];
  }

  /**
   * Data provider for testTitleTrimRestrictedToNodes.
   */
  public function titleTrimRestrictedToNodesDataProvider() {
    return [
      [
        'node',
        '   Spacey Stacey   ',
        'Spacey Stacey',
      ],
      [
        'term',
        '   Spacey Stacey   ',
        '   Spacey Stacey   ',
      ],
      [
        'user',
        '   Spacey Stacey   ',
        '   Spacey Stacey   ',
      ],
    ];
  }

}
