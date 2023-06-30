<?php

namespace tests\phpunit\va_gov_environment\unit\Form\Resolver;

use Drupal\va_gov_build_trigger\Form\BrdBuildTriggerForm;
use Drupal\va_gov_build_trigger\Form\LocalBuildTriggerForm;
use Drupal\va_gov_build_trigger\Form\TugboatBuildTriggerForm;
use Drupal\va_gov_content_release\Form\Resolver\Resolver;
use Drupal\va_gov_environment\Environment\Environment;
use Drupal\va_gov_environment\Service\DiscoveryInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Form Resolver service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Form\Resolver\Resolver
 */
class ResolverTest extends VaGovUnitTestBase {

  /**
   * Test that the reporter object will be constructed.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $environmentDiscoveryProphecy = $this->prophesize(DiscoveryInterface::class);
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $resolver = new Resolver($environmentDiscovery);
    $this->assertInstanceOf(Resolver::class, $resolver);
  }

  /**
   * Test the form class mapping.
   *
   * @param string $environmentId
   *   The environment ID.
   * @param string $formClass
   *   The expected form class.
   * @param \Throwable $exception
   *   The expected exception.
   *
   * @covers ::getFormClass
   * @dataProvider getFormClassProvider
   */
  public function testGetFormClass(string $environmentId, string $formClass = NULL, \Throwable $exception = NULL) {
    if ($exception) {
      $this->expectException(get_class($exception));
    }
    $environmentDiscoveryProphecy = $this->prophesize(DiscoveryInterface::class);
    $environmentDiscoveryProphecy->getEnvironment()->willReturn(Environment::from($environmentId));
    $environmentDiscovery = $environmentDiscoveryProphecy->reveal();
    $resolver = new Resolver($environmentDiscovery);
    $this->assertEquals($formClass, $resolver->getFormClass());
  }

  /**
   * Data provider for testGetFormClass().
   *
   * @return array
   *   The test data.
   */
  public function getFormClassProvider() {
    return [
      'ddev' => [
        'ddev',
        LocalBuildTriggerForm::class,
        NULL,
      ],
      'prod' => [
        'prod',
        BrdBuildTriggerForm::class,
        NULL,
      ],
      'staging' => [
        'staging',
        BrdBuildTriggerForm::class,
        NULL,
      ],
      'dev' => [
        'dev',
        BrdBuildTriggerForm::class,
        NULL,
      ],
      'tugboat' => [
        'tugboat',
        TugboatBuildTriggerForm::class,
        NULL,
      ],
      'invalid' => [
        'invalid',
        NULL,
        new \ValueError('Invalid environment ID: invalid'),
      ],
    ];
  }

}
