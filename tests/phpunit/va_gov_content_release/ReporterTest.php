<?php

namespace tests\phpunit\va_gov_environment;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\va_gov_content_release\Reporter\Reporter;
use Psr\Log\LoggerInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Reporter service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Reporter\Reporter
 */
class ReporterTest extends VaGovUnitTestBase {

  /**
   * Test that the reporter object will be constructed.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $messenger = $messengerProphecy->reveal();
    $loggerProphecy = $this->prophesize(LoggerInterface::class);
    $logger = $loggerProphecy->reveal();
    $loggerChannelFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerChannelFactoryProphecy->get('va_gov_content_release')->willReturn($logger);
    $loggerChannelFactory = $loggerChannelFactoryProphecy->reveal();
    $reporter = new Reporter($messenger, $loggerChannelFactory);
    $this->assertInstanceOf(Reporter::class, $reporter);
  }

  /**
   * Test that the reporter object will handle an error correctly.
   *
   * @covers ::reportError
   */
  public function testReportError() {
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $messengerProphecy->addError('message')->shouldBeCalled();
    $messenger = $messengerProphecy->reveal();
    $loggerProphecy = $this->prophesize(LoggerInterface::class);
    $loggerProphecy->error('message')->shouldBeCalled();
    $logger = $loggerProphecy->reveal();
    $loggerChannelFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerChannelFactoryProphecy->get('va_gov_content_release')->willReturn($logger);
    $loggerChannelFactory = $loggerChannelFactoryProphecy->reveal();
    $reporter = new Reporter($messenger, $loggerChannelFactory);
    $reporter->reportError('message');
  }

  /**
   * Test that the reporter object will handle an info message correctly.
   *
   * @covers ::reportInfo
   */
  public function testReportInfo() {
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $messengerProphecy->addStatus('message')->shouldBeCalled();
    $messenger = $messengerProphecy->reveal();
    $loggerProphecy = $this->prophesize(LoggerInterface::class);
    $loggerProphecy->info('message')->shouldBeCalled();
    $logger = $loggerProphecy->reveal();
    $loggerChannelFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerChannelFactoryProphecy->get('va_gov_content_release')->willReturn($logger);
    $loggerChannelFactory = $loggerChannelFactoryProphecy->reveal();
    $reporter = new Reporter($messenger, $loggerChannelFactory);
    $reporter->reportInfo('message');
  }

}
