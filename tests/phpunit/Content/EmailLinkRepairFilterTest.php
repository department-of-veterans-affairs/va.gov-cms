<?php

namespace tests\phpunit\Content;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm the proper functioning of the EmailLinkRepair filter.
 *
 * @group functional
 * @group all
 * @group filter
 *
 * @coversDefaultClass \Drupal\va_gov_backend\Plugin\Filter\EmailLinkRepairFilter
 */
class EmailLinkRepairFilterTest extends ExistingSiteBase {

  /**
   * Tests the filter's processing.
   *
   * @param string $input
   *   The input string (HTML).
   * @param string $expected
   *   The expected output, if different from the input.
   *
   * @covers EmailLinkRepairFilter::process
   * @dataProvider processDataProvider
   */
  public function testProcess(string $input, string $expected = NULL) {
    $filter = $this->container->get('plugin.manager.filter')->createInstance('va_gov_backend_email_link_repair');
    $langcode = 'en';
    $expected = $expected ?? $input;
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
        'test@example.org',
      ],
      [
        '<a id="test">test</a>',
      ],
      [
        '<a href="test@example.org">test</a>',
        '<a href="mailto:test@example.org">test</a>',
      ],
      [
        '<a href="test%40example.org">test</a>',
      ],
      [
        '<a href="/test@example.org">test</a>',
        '<a href="mailto:/test@example.org">test</a>',
      ],
      [
        '<a href="/test%40example.org">test</a>',
      ],
      [
        '<a href="/node/5313">test</a>',
      ],
      [
        '<a href="https://www.google.com/">test</a>',
      ],
      [
        '<a href="https://www.google.com/test@example.org">test</a>',
      ],
      [
        '<a href="https://www.google.com/test%40example.org">test</a>',
      ],
    ];
  }

}
