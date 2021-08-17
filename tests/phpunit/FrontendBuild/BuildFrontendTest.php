<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\va_gov_build_trigger\Service\BuildFrontend;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use Prophecy\Argument;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Functional test of the BuildFrontend class.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Service\BuildFrontend
 */
class BuildFrontendTest extends ExistingSiteBase {

  /**
   * Test triggerFrontendBuild()
   *
   * @param string $env
   *   Environment name, like 'lando', 'prod', etc.
   * @param bool $permitted
   *   Indicates whether the specified environment name is considered valid.
   *
   * @dataProvider triggerFrontendBuildDataProvider
   */
  public function testTriggerFrontendBuild(string $env, bool $permitted) {
    $realPermitted = $this->container->get('plugin.manager.va_gov.environment')->hasDefinition($env);
    $this->assertEquals($permitted, $realPermitted);

    $environmentDiscoveryProphecy = $this->prophesize(EnvironmentDiscovery::class);

    $loggerProphecy = $this->prophesize(LoggerChannelInterface::class);
    $messengerProphecy = $this->prophesize(MessengerInterface::class);
    $webBuildStatusProphecy = $this->prophesize(WebBuildStatusInterface::class);
    $webBuildStatusProphecy->enableWebBuildStatus()->shouldNotBeCalled();
    if ($permitted) {
      $environmentDiscoveryProphecy->triggerFrontendBuild(Argument::any(), Argument::any())->shouldBeCalled();
      $loggerProphecy->warning(Argument::any())->shouldNotBeCalled();
      $messengerProphecy->addWarning(Argument::any())->shouldNotBeCalled();
      $webBuildStatusProphecy->disableWebBuildStatus()->shouldNotBeCalled();
    }
    else {
      $environmentDiscoveryProphecy->triggerFrontendBuild(Argument::any(), Argument::any())->willThrow(PluginException::class);
      $loggerProphecy->warning(Argument::type(TranslatableMarkup::class))->shouldBeCalled();
      $messengerProphecy->addWarning(Argument::type(TranslatableMarkup::class))->shouldBeCalled();
      $webBuildStatusProphecy->disableWebBuildStatus()->shouldBeCalled();
    }

    $messenger = $messengerProphecy->reveal();
    $logger = $loggerProphecy->reveal();
    $loggerFactoryProphecy = $this->prophesize(LoggerChannelFactoryInterface::class);
    $loggerFactoryProphecy->get(Argument::type('string'))->willReturn($logger);
    $loggerFactory = $loggerFactoryProphecy->reveal();
    $webBuildStatus = $webBuildStatusProphecy->reveal();

    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $buildFrontend = new BuildFrontend($messenger, $loggerFactory, $webBuildStatus, $environmentDiscovery);

    $buildFrontend->triggerFrontendBuild('no', FALSE);
  }

  /**
   * Data provider for ::testTriggerFrontendBuild().
   */
  public function triggerFrontendBuildDataProvider() {
    return [
      [
        'lando',
        TRUE,
      ],
      [
        'tugboat',
        TRUE,
      ],
      [
        'brd',
        TRUE,
      ],
      [
        'test',
        FALSE,
      ],
    ];
  }

}
