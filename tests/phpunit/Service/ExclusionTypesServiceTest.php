<?php

namespace tests\phpunit\Service;

use Composer\Autoload\ClassLoader;
use Drupal\va_gov_backend\Service\ExclusionTypes;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Test the ExclusionTypes service.
 *
 * @group functional
 * @group all
 */
class ExclusionTypesServiceTest extends VaGovUnitTestBase {

  /**
   * The mocked config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The tested ExclusionTypes service.
   *
   * @var \Drupal\va_gov_backend\Service\ExclusionTypes|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $exclusionTypes;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();
    $this->configFactory = $this->getConfigFactoryStub([
      'va_gov_backend.exclusion_settings' => [
        'types_to_exclude' => [
          'page' => 'page',
          'office' => 'office',
        ],
      ],
    ]);
    $this->exclusionTypes = new ExclusionTypes($this->configFactory);

    $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
    $container->expects($this->any())
      ->method('get')
      ->with('class_loader')
      ->will($this->returnValue($this->createMock(ClassLoader::class)));
    \Drupal::setContainer($container);
  }

  /**
   * Verify getExcludedTypes method.
   */
  public function testGetExcludedTypes() {
    $this->assertEquals(['page' => 'page', 'office' => 'office'], $this->exclusionTypes->getExcludedTypes());
  }

  /**
   * Verify getJson method.
   */
  public function testGetJson() {
    $this->assertEquals('{"page":"page","office":"office"}', $this->exclusionTypes->getJson());
  }

  /**
   * Verify typeIsExcluded method.
   */
  public function testTypeIsExcluded() {
    $this->assertTrue($this->exclusionTypes->typeIsExcluded('page'));
    $this->assertTrue($this->exclusionTypes->typeIsExcluded('office'));
    $this->assertFalse($this->exclusionTypes->typeIsExcluded('person_profile'));
  }

}
