<?php

namespace test\phpunit\Deploy;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_backend\Logger\Processor\DatadogApmProcessor;
use Drupal\va_gov_backend\Service\DatadogContextProviderInterface;

/**
 * @covers \Drupal\va_gov_backend\Logger\Processor\DatadogApmProcessor
 */
class DatadogApmProcessorTest extends UnitTestCase {

  /**
   * Test ::getCurrentContext().
   *
   * @covers \Drupal\va_gov_backend\Logger\Processor\DatadogApmProcessor::getCurrentContext()
   */
  public function testGetCurrentContext() {
    $expectedTraceId = 4538051125887805679;
    $expectedSpanId = 9223372036854775807;
    $contextProviderProphecy = $this->prophesize(DatadogContextProviderInterface::CLASS);
    $contextProviderProphecy->getCurrentContext()->willReturn([
      'trace_id' => $expectedTraceId,
      'span_id' => $expectedSpanId,
    ]);
    $contextProvider = $contextProviderProphecy->reveal();
    $processor = new DatadogApmProcessor($contextProvider);
    $actual = $processor->getCurrentContext();
    $this->assertEquals($expectedTraceId, $actual['trace_id']);
    $this->assertEquals($expectedSpanId, $actual['span_id']);
  }

  /**
   * Test ::getAlteredMessage().
   *
   * @covers \Drupal\va_gov_backend\Logger\Processor\DatadogApmProcessor::getAlteredMessage()
   */
  public function testGetAlteredMessage() {
    // Chosen by fair dice roll. Guaranteed to be random.
    $expectedTraceId = 6;
    $expectedSpanId = 3;
    $contextProviderProphecy = $this->prophesize(DatadogContextProviderInterface::CLASS);
    $contextProviderProphecy->getCurrentContext()->willReturn([
      'trace_id' => $expectedTraceId,
      'span_id' => $expectedSpanId,
    ]);
    $contextProvider = $contextProviderProphecy->reveal();
    $processor = new DatadogApmProcessor($contextProvider);
    $message = "I'm holding in my hand a small box of chocolate bunnies.";
    $actual = $processor->getAlteredMessage($message, $processor->getCurrentContext());
    $this->assertTrue(strpos($actual, $message) !== FALSE);
    $this->assertTrue(strpos($actual, 'dd.trace_id=' . $expectedTraceId) !== FALSE);
    $this->assertTrue(strpos($actual, 'dd.span_id=' . $expectedSpanId) !== FALSE);
  }

  /**
   * Test ::getAlteredRecord().
   *
   * @covers \Drupal\va_gov_backend\Logger\Processor\DatadogApmProcessor::getAlteredRecord()
   */
  public function testGetAlteredRecord() {
    $expectedTraceId = 6;
    $expectedSpanId = 3;
    $contextProviderProphecy = $this->prophesize(DatadogContextProviderInterface::CLASS);
    $contextProviderProphecy->getCurrentContext()->willReturn([
      'trace_id' => $expectedTraceId,
      'span_id' => $expectedSpanId,
    ]);
    $contextProvider = $contextProviderProphecy->reveal();
    $processor = new DatadogApmProcessor($contextProvider);
    $message = "This is what we found in his trunk: novocaine, machine gun, dog leg.";
    $record = [
      'message' => $message,
    ];
    $actual = $processor->getAlteredRecord($record, $processor->getCurrentContext());
    $this->assertTrue(strpos($actual['message'], $message) !== FALSE);
    $this->assertTrue(strpos($actual['message'], 'dd.trace_id=' . $expectedTraceId) !== FALSE);
    $this->assertTrue(strpos($actual['message'], 'dd.span_id=' . $expectedSpanId) !== FALSE);
    $this->assertEquals($actual['dd'], [
      'trace_id' => $expectedTraceId,
      'span_id' => $expectedSpanId,
    ]);
  }

  /**
   * Test ::hasAlteredRecord().
   *
   * @covers \Drupal\va_gov_backend\Logger\Processor\DatadogApmProcessor::hasAlteredRecord()
   */
  public function testHasAlteredRecord() {
    $expectedTraceId = 6;
    $expectedSpanId = 3;
    $contextProviderProphecy = $this->prophesize(DatadogContextProviderInterface::CLASS);
    $contextProviderProphecy->getCurrentContext()->willReturn([
      'trace_id' => $expectedTraceId,
      'span_id' => $expectedSpanId,
    ]);
    $contextProvider = $contextProviderProphecy->reveal();
    $processor = new DatadogApmProcessor($contextProvider);
    $message = "Pie. Whoever invented the pie? Here was a great person.";
    $record = [
      'message' => $message,
    ];
    $this->assertTrue(!$processor->hasAlteredRecord($record));
    $actual = $processor->getAlteredRecord($record, $processor->getCurrentContext());
    $this->assertTrue($processor->hasAlteredRecord($actual));
  }

  /**
   * Test ::shouldAlterRecord().
   *
   * @covers \Drupal\va_gov_backend\Logger\Processor\DatadogApmProcessor::shouldAlterRecord()
   */
  public function testShouldAlterRecord() {
    $expectedTraceId = 6;
    $expectedSpanId = 3;
    $contextProviderProphecy = $this->prophesize(DatadogContextProviderInterface::CLASS);
    $contextProviderProphecy->getCurrentContext()->willReturn([
      'trace_id' => $expectedTraceId,
      'span_id' => $expectedSpanId,
    ]);
    $contextProvider = $contextProviderProphecy->reveal();
    $processor = new DatadogApmProcessor($contextProvider);
    $message = "YOU ARE WITNESSING A FRONT THREE-QUARTER VIEW OF TWO ADULTS SHARING A TENDER MOMENT.";
    $record = [
      'message' => $message,
    ];
    $this->assertTrue($processor->shouldAlterRecord($record));
    $actual = $processor->getAlteredRecord($record, $processor->getCurrentContext());
    $this->assertTrue(!$processor->shouldAlterRecord($actual));
  }

}
