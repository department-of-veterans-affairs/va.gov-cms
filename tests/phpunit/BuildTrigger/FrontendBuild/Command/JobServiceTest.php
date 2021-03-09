<?php

namespace tests\phpunit\BuildTrigger\FrontendBuild\Command;

use Drupal\KernelTests\KernelTestBase;

/**
 * Kernel tests for the web build command job service.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Service\WebBuildCommandJobService
 */
class JobServiceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'datetime',
    'va_gov_build_trigger',
  ];

  /**
   * Test converting between jobs and commands.
   *
   * @covers ::getJob
   * @covers ::getCommands
   */
  public function testGetJob() {
    $service = $this->container->get('va_gov_build_trigger.web_build_command_job');
    $commands = [
      'whoami',
      'ls /',
    ];
    $job = $service->getJob($commands);
    $this->assertEquals($commands, $service->getCommands($job));
  }

}
