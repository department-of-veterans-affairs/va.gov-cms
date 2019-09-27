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

//    /**
//     * @param \Composer\Script\CommandEvent $event
//     */
//    public static function postUpdateCmd(Event $event) {
//
//        $package = $event->getComposer()->getRepositoryManager()->findPackage('va-gov/web', "*");
//        self::printLine("Package Found: " . $package->getPrettyString());
//
//        // Prevent infinite loops
//        static $run_once;
//
//        if ($run_once) {
//          return;
//        }
//
//
//        // Read the REF directly from git repo.
//        $git_url = self::REPOSITORY_URL;
//        $git_branch = self::REPOSITORY_BRANCH;
//        $package = self::PACKAGE_NAME;
//
//        $output = trim(shell_exec("git ls-remote {$git_url} {$git_branch}"));
//        list($git_sha) = explode("	", $output);
//
//        $cmd = "composer require {$package}:dev-{$git_branch}#{$git_sha}";
//        self:self::printLine("Maybe we can run $cmd ? ...");
////        shell_exec($cmd);
//
//      $run_once = TRUE;
//    }

    /**
     * @param \Composer\Plugin\PreCommandRunEvent $event
     */
    public static function preDepSolve(InstallerEvent &$event) {
      if (file_exists(sys_get_temp_dir() . '/va-gov-updated')) {
        unlink(sys_get_temp_dir() . '/va-gov-updated');
        return;
      }

      $repo =  $event->getInstalledRepo();

      $package = $repo->findPackage('va-gov/web', "dev-master");

      self::printLine("Found package " . $package->getPrettyString());
      self::printLine("Source Ref " . $package->getSourceReference());

      self::printLine("Looking up master SHA of vets-website... ");
      $sha = self::getWebSha();
      self::printLine("SHA Found: " . $sha);

      if (strpos($package->getSourceReference(), $sha) !== 0) {
        self::printLine("Local version is not the latest. Updating to " . $sha);
      }

      // @TODO: How to save this data in composer's metadata?
      $package->setSourceReference($sha);
      $package->setDistReference($sha);
      $package->setInstallationSource('path');

      self::printLine("Set Source & Dist Reference of the package: " .       $package->getSourceReference());
      touch(sys_get_temp_dir() . '/va-gov-updated');
    }

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


