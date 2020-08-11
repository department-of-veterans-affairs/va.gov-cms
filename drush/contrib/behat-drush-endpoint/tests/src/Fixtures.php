<?php

namespace TestUtils;

use Symfony\Component\Filesystem\Filesystem;

use Drush\TestTraits\DrushTestTrait;
use Webmozart\PathUtil\Path;

/**
 * Convenience class for creating fixtures.
 */
class Fixtures
{
  use DrushTestTrait;

  protected static $fixtures = null;
  protected $installed = false;

  public static function instance()
  {
      if (!static::$fixtures) {
          static::$fixtures = new self();
      }
      return static::$fixtures;
  }

  public function createSut()
  {
      // Skip install if already installed.
      if ($this->installed || getenv('SI_SKIP')) {
          return;
      }
      // @todo: pull db credentials from phpunit.xml configuration

      $this->createRequiredFiles();

      // Make settings.php writable again
      chmod('sut/web/sites/default/', 0755);
      @unlink('sut/web/sites/default/settings.php');
      copy('sut/web/sites/default/default.settings.php', 'sut/web/sites/default/settings.php');

      // Run site-install (Drupal makes settings.php unwritable)
      $this->drush('site-install', ['--yes'], ['db-url' => 'mysql://root@127.0.0.1/testdrushextensiondb']);

      $this->installed = true;
  }

  protected function createRequiredFiles()
  {
    $fs = new Filesystem();
    $drupalRoot = 'sut/web';

    $dirs = [
      'modules',
      'profiles',
      'themes',
    ];

    // Required for unit testing
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupalRoot . '/'. $dir)) {
        $fs->mkdir($drupalRoot . '/'. $dir);
        $fs->touch($drupalRoot . '/'. $dir . '/.gitkeep');
      }
    }

    // Prepare the settings file for installation
    if (!$fs->exists($drupalRoot . '/sites/default/settings.php') and $fs->exists($drupalRoot . '/sites/default/default.settings.php')) {
      $fs->copy($drupalRoot . '/sites/default/default.settings.php', $drupalRoot . '/sites/default/settings.php');
      require_once $drupalRoot . '/core/includes/bootstrap.inc';
      require_once $drupalRoot . '/core/includes/install.inc';
      $settings['config_directories'] = [
        CONFIG_SYNC_DIRECTORY => (object) [
          'value' => Path::makeRelative('config/sync', $drupalRoot),
          'required' => TRUE,
        ],
      ];
      drupal_rewrite_settings($settings, $drupalRoot . '/sites/default/settings.php');
      $fs->chmod($drupalRoot . '/sites/default/settings.php', 0666);
    }

    // Create the files directory with chmod 0777
    if (!$fs->exists($drupalRoot . '/sites/default/files')) {
      $oldmask = umask(0);
      $fs->mkdir($drupalRoot . '/sites/default/files', 0777);
      umask($oldmask);
    }
  }

  /**
   * Directories to delete when we are done.
   *
   * @var string[]
   */
  protected $tmpDirs = [];

  /**
   * Gets the path to the project fixtures.
   *
   * @return string
   *   Path to project fixtures
   */
  public function allFixturesDir()
  {
    return realpath(__DIR__ . '/fixtures');
  }

  /**
   * Generates a path to a temporary location, but do not create the directory.
   *
   * @param string $extraSalt
   *   Extra characters to throw into the md5 to add to name.
   *
   * @return string
   *   Path to temporary directory
   */
  public function tmpDir($extraSalt = '')
  {
    $tmpDir = sys_get_temp_dir() . '/site-audit-test-' . md5($extraSalt . microtime());
    $this->tmpDirs[] = $tmpDir;
    return $tmpDir;
  }

  /**
   * Creates a temporary directory.
   *
   * @param string $extraSalt
   *   Extra characters to throw into the md5 to add to name.
   *
   * @return string
   *   Path to temporary directory
   */
  public function mkTmpDir($extraSalt = '')
  {
    $tmpDir = $this->tmpDir($extraSalt);
    $filesystem = new Filesystem();
    $filesystem->ensureDirectoryExists($tmpDir);
    return $tmpDir;
  }

  /**
   * Calls 'tearDown' in any test that copies fixtures to transient locations.
   */
  public function tearDown()
  {
    // Remove any temporary directories that were created.
    $filesystem = new Filesystem();
    foreach ($this->tmpDirs as $dir) {
      $filesystem->remove($dir);
    }
    // Clear out variables from the previous pass.
    $this->tmpDirs = [];
    $this->io = NULL;
  }

}
