<?php

namespace VA\Composer;

use Composer\EventDispatcher\ScriptExecutionException;
use Composer\Installer\InstallerEvent;
use Composer\Repository\BaseRepository;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryFactory;
use Composer\Script\Event;
use Composer\Plugin\PreCommandRunEvent;
use Composer\Installer\PackageEvent;
use Composer\Semver\Constraint\Constraint;
use Drupal\Tests\config_translation\Kernel\Migrate\d6\MigrateUserProfileFieldInstanceTranslationTest;
use Github\Client;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Process\Process;
use TQ\Git\Repository\Repository;

/**
 * Special update handling for vets-website
 */
class UpdateHandler {

    const REPOSITORY_NAME = 'department-of-veterans-affairs/vets-website';
    const REPOSITORY_URL = 'https://github.com/department-of-veterans-affairs/vets-website';
    const REPOSITORY_BRANCH = 'master';
    const PACKAGE_NAME = 'va-gov/web';
    const WEB_PATH = 'web';

    const CLI_PREFIX = 'UpdateHandler.php | ';

    /**
     * Runs before composer update does dependency lookup: This forces
     * va-gov/web to use the SHA we detect from the desired branch.
     *
     * At the end of this method, $package->setSourceReference($sha) is called.
     * The $sha set for sourceReference is used in composer.lock!
     *
     * @param InstallerEvent $event
     */
    public static function preDepSolve(InstallerEvent &$event) {
      // Prevent double runs.
      $temp_file = sys_get_temp_dir() . '/va-gov-updated';
      if (file_exists($temp_file)) {
        unlink($temp_file);
        return;
      }

      // Look for the package.
      $package = $event->getInstalledRepo()->findPackage('va-gov/web', "dev-master");
      if (!$package) {
        return;
      }

      self::printLine("Found package " . $package->getPrettyString());
      self::printLine("Source Ref " . $package->getSourceReference());

      // Load origin/master SHA.
      self::printLine("Looking up SHA of vets-website... ");
      $sha = self::getWebSha();
      self::printLine("      SHA Found: " . $sha);

      if (strpos($package->getSourceReference(), $sha) !== 0) {
        self::printLine("Source version is not the same as latest SHA: " . $sha);

        $package->setSourceReference($sha);
        $package->setDistReference($sha);

        self::printLine("Set Source & Dist Reference of the package. SHA will be written to composer.lock.");
        touch($temp_file);
      }
    }

  /**
   * @param \Composer\Plugin\PreCommandRunEvent $event
   */
//  public static function postDepSolve(InstallerEvent &$event) {

//    print "huh";


//  }

  /**
   * Return the SHA from origin/master.
   * @param string $branch
   *
   * @return mixed
   */
    public static function getWebSha($branch = 'master') {
      // Read the REF directly from git repo.
      $git_url = self::REPOSITORY_URL;
      $git_branch = self::REPOSITORY_BRANCH;

      $output = trim(shell_exec("git ls-remote {$git_url} {$git_branch}"));
      list($git_sha) = explode("	", $output);
      return $git_sha;
    }

    /**
     * @param $string
     */
    static function printLine($string) {
        print self::CLI_PREFIX . $string . "\n";
    }
}


