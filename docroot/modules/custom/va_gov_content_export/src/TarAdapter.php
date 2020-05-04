<?php

namespace Drupal\va_gov_content_export;

use Alchemy\Zippy\Adapter\AdapterContainer;
use Alchemy\Zippy\Adapter\GNUTar\TarGNUTarAdapter;
use Alchemy\Zippy\Archive\Archive;
use Alchemy\Zippy\Exception\RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;

/**
 * Class TarAdapter.
 */
class TarAdapter extends TarGNUTarAdapter {

  /**
   * Get an instance of TarAdapter.
   *
   * @return \Drupal\va_gov_content_export\TarAdapter
   *   The instance of TarAdapter.
   */
  public static function get() : TarAdapter {
    $container = AdapterContainer::load();
    $container['va_gov_cms_tar'] = function ($container) {
      return TarAdapter::newInstance(
        $container['executable-finder'],
        $container['resource-manager'],
        $container['gnu-tar.inflator'],
        $container['gnu-tar.deflator']
      );
    };

    return $container['va_gov_cms_tar'];
  }

  /**
   * {@inheritDoc}
   */
  protected function doTarCreate($options, $path, $files = NULL, $recursive = TRUE) {
    // The $files are seperated into $files['exclude'] and $files['path']
    // $files['exclude'] (array) are the files/paths to exculde to the tar
    // $files['path'] (string) is the path to tar up.
    // $files['cwd'] (string) is the base path of the tared up files.
    // Notes these have to be full paths and not uri's currently.
    $exclude_files = $files['exclude'] ?? [];
    $tar_dir = $files['path'] ?? '';

    $cwd = $files['cwd'] ?? $files['path'] ?? getcwd();

    if (!$tar_dir) {
      throw new RuntimeException('Path must be included');
    }

    // $files are actually directories to exclude.
    $builder = $this
      ->inflator
      ->create();

    if (!$recursive) {
      $builder->add('--no-recursion');
    }

    $builder->add('--create');

    foreach ((array) $options as $option) {
      $builder->add((string) $option);
    }

    if (0 === count($files)) {
      $nullFile = defined('PHP_WINDOWS_VERSION_BUILD') ? 'NUL' : '/dev/null';

      $builder->add('-T');
      $builder->add($nullFile);

      $process = $builder->getProcess();
      $process->run();

    }
    else {
      if (!$recursive) {
        $builder->add('--no-recursion');
      }

      if ($cwd) {
        $builder->setWorkingDirectory($cwd);
      }

      // Do not include parent directory.
      $builder->add(sprintf('--directory=%s', $cwd));

      foreach ($exclude_files as $file) {
        $builder->add(sprintf('--exclude=%s', $file));
      }

      $builder->add(sprintf('--file=%s', $path));
      $builder->add($tar_dir);

      $process = $builder->getProcess();

      try {
        $process->run();
      }
      catch (ProcessException $e) {
        throw $e;
      }
    }

    if (!$process->isSuccessful()) {
      throw new RuntimeException(sprintf(
        'Unable to execute the following command %s {output: %s}',
        $process->getCommandLine(),
        $process->getErrorOutput()
      ));
    }

    return new Archive($this->createResource($path), $this, $this->manager);
  }

}
