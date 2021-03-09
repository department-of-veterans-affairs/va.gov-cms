<?php

namespace tests\phpunit\BuildTrigger\Command;

use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_build_trigger\Command\CommandRunner;

/**
 * Test the Command Runner trait.
 *
 * @covers \tests\phpunit\BuildTrigger\Command\TestClass
 */
class CommandRunnerTest extends UnitTestCase {

  /**
   * Run some commands and see what happens.
   *
   * @param array $commands
   *   An array of commands.
   * @param int $concurrency
   *   The level of concurrency at which the commands should be run.
   * @param int $retryCount
   *   The number of times to retry a failed command.
   * @param callable|null $callback
   *   A callback to invoke for each completed command.
   * @param array $expected
   *   An array of expected errors that would be encountered.
   * @covers \tests\phpunit\BuildTrigger\Command\TestClass::runCommands
   * @dataProvider runCommandsDataProvider
   */
  public function testRunCommands(array $commands, int $concurrency, int $retryCount, callable $callback = NULL, array $expected) {
    $testObject = new TestClass();
    $errors = $testObject->runCommands($commands, $concurrency, $retryCount, $callback);
    $this->assertEquals($errors, $expected);
  }

  /**
   * Data provider for ::testRunCommands.
   *
   * @return array
   *   The test cases and their expected errors.
   */
  public function runCommandsDataProvider() : array {
    return [
      [
        [],
        1,
        0,
        NULL,
        [],
      ],
      [
        [
          'TEMP_DIR=$(mktemp -d | xargs) && cd "$TEMP_DIR" && if [ "$(pwd)" = "$TEMP_DIR" ]; then echo "MATCH"; fi;',
        ],
        1,
        0,
        function ($process) {
          $this->assertEquals('MATCH', trim($process->getOutput()));
        },
        [],
      ],
      [
        [
          'export TEMP_DIR=$(mktemp -d | xargs)',
          'cd "$TEMP_DIR"',
          'if [ "$(pwd)" = "$TEMP_DIR" ]; then echo "MATCH"; fi;',
        ],
        1,
        0,
        function ($process) {
          $this->assertEquals('', trim($process->getOutput()));
        },
        [],
      ],
    ];
  }

}

/**
 * Test class.
 */
class TestClass {
  use CommandRunner;

}
