<?php

namespace tests\phpunit\va_gov_git\unit\Repository\Factory;

use Drupal\va_gov_git\Repository\Factory\RepositoryFactory;
use Drupal\va_gov_git\Repository\Settings\RepositorySettingsInterface;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Repository Factory service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_git\Repository\Factory\RepositoryFactory
 */
class RepositoryFactoryTest extends VaGovUnitTestBase {

  /**
   * Test construction.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $repositoryFactory = new RepositoryFactory($this->createMock(RepositorySettingsInterface::class));
    $this->assertInstanceOf(RepositoryFactory::class, $repositoryFactory);
  }

}
