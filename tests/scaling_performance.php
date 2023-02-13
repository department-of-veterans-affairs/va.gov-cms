<?php

/**
 * @file
 * Perform scalability tests.
 */

use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use Symfony\Component\Process\Process;

require_once __DIR__ . '../docroot/autoload.php';

/**
 * Run the test.
 */
function run_test(int $start_time, string $cmd, int $id) {
  echo $id . ': Waiting on start time' . PHP_EOL;
  while (time() < $start_time) {
  }

  echo $id . ': Staring test' . PHP_EOL;

  try {
    $process = new Process($cmd);
    $process->run();
  }
  catch (ProcessException $e) {
  }

  if (!$process->isSuccessful()) {
    echo $id . ' ERROR ######################' . PHP_EOL;

    echo sprintf(
      'Unable to execute the following command %s {output: %s}',
      $process->getCommandLine(),
      $process->getErrorOutput()
    );

    echo $process->getOutput();
  }

  if ($process->isSuccessful()) {
    echo $id . ' SUCCESS ######################' . PHP_EOL;
    echo $process->getErrorOutput();
    echo $process->getOutput();
  }
}

$start_time = time() + 10;
$number_of_forks = 5;
$cmd = '/app/bin/phpunit --debug --exclude-group disabled --verbose --colors=always tests/phpunit/ScalabilityCreateNodeTest.php';

$pids = [];
for ($i = 0; $i < $number_of_forks; $i++) {
  switch ($pid = pcntl_fork()) {
    case -1:
      // @fail
      die('Fork failed');

    case 0:
      // @child: Include() misbehaving code here
      run_test($start_time, $cmd, $i);
      echo 'exit child ' . $i;
      exit();

    default:
      $pids[] = $pid;
      break;
  }
}

foreach ($pids as $pid) {
  echo 'Waiting on ' . $pid . PHP_EOL;
  pcntl_waitpid($pid, $status);
  echo $pid . ' complete' . PHP_EOL;
}
