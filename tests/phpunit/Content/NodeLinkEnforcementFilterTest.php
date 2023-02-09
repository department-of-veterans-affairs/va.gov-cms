<?php

namespace tests\phpunit\Content;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\va_gov_backend\Plugin\Filter\NodeLinkEnforcementFilter;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * A test to confirm the proper functioning of the NodeLinkEnforcement filter.
 *
 * @group unit
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Filter\NodeLinkEnforcementFilter
 */
class NodeLinkEnforcementFilterTest extends VaGovUnitTestBase {

  /**
   * Retrieve a valid filter instance.
   */
  public function getFilter(string $nid, string $uuid): NodeLinkEnforcementFilter {

    $nodeProphecy = $this->prophesize(NodeInterface::CLASS);
    $nodeProphecy->id()->willReturn($nid);
    $nodeProphecy->uuid()->willReturn($uuid);

    $nodeStorageProphecy = $this->prophesize(NodeStorageInterface::CLASS);
    $nodeStorageProphecy
      ->load($nid)
      ->willReturn($nodeProphecy->reveal());

    $entityTypeManagerProphecy = $this->prophesize(EntityTypeManagerInterface::CLASS);
    $entityTypeManagerProphecy
      ->getStorage('node')
      ->willReturn($nodeStorageProphecy->reveal());

    return new NodeLinkEnforcementFilter(
      [],
      'va_gov_backend_node_link_enforcement',
      [
        'provider' => 'deez tests',
      ],
      $entityTypeManagerProphecy->reveal()
    );
  }

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
    $nid = '14234';
    $uuid = 'FC0B78FF-85FF-4769-A3C7-8F6B0480472D';
    $patterns = [
      '__NODE_ID__' => $nid,
      '__NODE_UUID__' => $uuid,
    ];
    $filter = $this->getFilter($nid, $uuid);
    $langcode = 'en';
    $expected = $expected ?? $input;
    $input = str_replace(array_keys($patterns), array_values($patterns), $input);
    $expected = str_replace(array_keys($patterns), array_values($patterns), $expected);
    $this->assertEquals($expected, $filter->process($input, $langcode)->getProcessedText());
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
