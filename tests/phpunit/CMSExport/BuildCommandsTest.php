<?php

namespace test\phpunit\CMSExport;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test the BuildCommands class.
 */
class BuildCommandsTest extends ExistingSiteBase {

  /**
   * @covers \Drupal\va_gov_content_export\ExportCommand\BuildCommands::buildCommands
   */
  public function testbuildCommands() {
    /** @var \Drupal\va_gov_content_export\ExportCommand\BuildCommands $build_commands */
    $build_commands = \Drupal::service('va_gov.content_export.export_all_command');
    static::assertNotNull($build_commands, 'BuildCommands instance was created.');

    $commands = $build_commands->buildCommands(200);
    $command = reset($commands);
    self::assertStringNotContainsString('--export-dir=', $command);

    $build_commands->setExportDir('bob');
    $commands = $build_commands->buildCommands(200);
    $command = reset($commands);
    self::assertStringContainsString("--export-dir='bob'", $command);
  }

}
