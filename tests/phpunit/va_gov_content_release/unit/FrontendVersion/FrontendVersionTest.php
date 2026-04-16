<?php

namespace tests\phpunit\va_gov_content_release\unit\FrontendVersion;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\State\State;
use Drupal\va_gov_content_release\Frontend\Frontend;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersion;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Frontend Version service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\FrontendVersion\FrontendVersion
 */
class FrontendVersionTest extends VaGovUnitTestBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    \Drupal::unsetContainer();
    $container = new ContainerBuilder();

    $state = $this->getMockBuilder('Drupal\Core\State\StateInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $container->set('state', $state);
    $container->set('cache.bootstrap', $this->getMockBuilder('Drupal\Core\Cache\CacheBackendInterface')->getMock());
    $container->set('lock', $this->getMockBuilder('Drupal\Core\Lock\LockBackendInterface')->getMock());

    \Drupal::setContainer($container);

    $this->state = new State(new KeyValueMemoryFactory());
  }

  /**
   * Test that the service works as expected.
   *
   * @covers ::__construct
   * @covers ::getVersion
   * @covers ::setVersion
   * @covers ::resetVersion
   */
  public function testGetSetReset() : void {
    $frontend = Frontend::ContentBuild;
    $frontendVersion = new FrontendVersion($this->state);
    $this->assertEquals(FrontendVersionInterface::FRONTEND_VERSION_DEFAULT, $frontendVersion->getVersion($frontend));
    $frontendVersion->setVersion($frontend, '1.2.3');
    $this->assertEquals('1.2.3', $frontendVersion->getVersion($frontend));
    $frontendVersion->resetVersion($frontend);
    $this->assertEquals(FrontendVersionInterface::FRONTEND_VERSION_DEFAULT, $frontendVersion->getVersion($frontend));
  }

}
