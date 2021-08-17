<?php

namespace tests\phpunit\Content;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm the proper functioning of the NodeLinkEnforcement filter.
 *
 * @group functional
 * @group all
 * @group filter
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Filter\NodeLinkEnforcementFilter
 */
class NodeLinkEnforcementFilterTest extends ExistingSiteBase {

  /**
   * Test node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Tests the filter's processing.
   *
   * @param string $input
   *   The input string (HTML).
   * @param string $expected
   *   The expected output, if different from the input.
   *
   * @covers NodeLinkEnforcementFilter::process
   * @dataProvider processDataProvider
   */
  public function testProcess(string $input, string $expected = NULL) {
    $node = $this->getTestNode();
    $patterns = [
      '__NODE_ID__' => $node->id(),
      '__NODE_UUID__' => $node->uuid(),
    ];
    $filter = $this->container->get('plugin.manager.filter')->createInstance('va_gov_backend_node_link_enforcement');
    $langcode = 'en';
    $expected = $expected ?? $input;
    $input = str_replace(array_keys($patterns), array_values($patterns), $input);
    $expected = str_replace(array_keys($patterns), array_values($patterns), $expected);
    $this->assertEquals($expected, $filter->process($input, $langcode)->getProcessedText());
  }

  /**
   * Retrieve a test node.
   *
   * @return \Drupal\node\NodeInterface
   *   A test node.
   */
  public function getTestNode() {
    if (empty($this->node)) {
      $author = $this->createUser();
      $author->addRole('content_editor');
      $author->save();
      $this->drupalLogin($author);
      $name = uniqid();
      $node = $this->createNode([
        'title' => __FILE__ . ' test',
        'type' => 'page',
        'uid' => $author->id(),
      ]);
      $node->setPublished()->save();
      $this->node = $node;
    }
    return $this->node;
  }

  /**
   * Data Provider for ::testProcess().
   *
   * The first value of each pair is the input, the second the expected output.
   *
   * If the second value is not provided, it is expected that no changes will be
   * made to the input.
   */
  public function processDataProvider() {
    return [
      [
        'Some innocuous text with some <b>bold</b> text.',
      ],
      [
        '/node/__NODE_ID__',
      ],
      [
        '<a id="test">test</a>',
      ],
      [
        '<a href="node/__NODE_ID__">test</a>',
      ],
      [
        '<a href="node/text">test</a>',
      ],
      [
        '<a href="node/230947230952309842039842309842">test</a>',
      ],
      [
        '<a href="/node/__NODE_ID__">test</a>',
        '<a href="/node/__NODE_ID__" data-entity-type="node" data-entity-substitution="canonical" data-entity-uuid="__NODE_UUID__">test</a>',
      ],
      [
        '<a href="/node/__NODE_ID__" data-entity-type="node">test</a>',
        '<a href="/node/__NODE_ID__" data-entity-type="node" data-entity-substitution="canonical" data-entity-uuid="__NODE_UUID__">test</a>',
      ],
      [
        '<a href="/node/__NODE_ID__" data-entity-substitution="canonical">test</a>',
        '<a href="/node/__NODE_ID__" data-entity-substitution="canonical" data-entity-type="node" data-entity-uuid="__NODE_UUID__">test</a>',
      ],
      [
        '<a href="/node/__NODE_ID__" data-entity-type="node" data-entity-substitution="canonical">test</a>',
        '<a href="/node/__NODE_ID__" data-entity-type="node" data-entity-substitution="canonical" data-entity-uuid="__NODE_UUID__">test</a>',
      ],
      [
        '<a href="/node/__NODE_ID__" data-entity-type="node" data-entity-substitution="canonical" data-entity-uuid="__NODE_UUID__">test</a>',
      ],
    ];
  }

}
