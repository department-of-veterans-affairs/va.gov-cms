<?php

namespace VA\Composer;

use Composer\Installer\InstallerEvent;
use Composer\Plugin\PreCommandRunEvent;
use Composer\Installer\PackageEvent;
use Composer\Semver\Constraint\Constraint;
use Drupal\Tests\config_translation\Kernel\Migrate\d6\MigrateUserProfileFieldInstanceTranslationTest;
use Github\Client;
use Symfony\Component\Console\Formatter\OutputFormatter;
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

    public static function preDepSolve(InstallerEvent $event) {

            // Check for git repo in WEB_PATH
            $git = Repository::open(self::WEB_PATH);
            self::printLine("Composer Update Requested... Checking Looking up SHA for master branch in vets-website...");
            self::printLine("Git repository found in {$git->getRepositoryPath()} ...");

            // @TODO: Read the branch from composer?
            $result = $git->getGit()->{'ls-remote'}($git->getRepositoryPath(), array(
              self::REPOSITORY_URL,
              'master'
            ));
            // Bust up the git ls-remote output.
            //         NOTE: Not a space! ⮷  Do not change.
            list($sha) = explode("	", ($result->getStdOut()));
            $branch = self::REPOSITORY_BRANCH;
            self::printLine("Found the latest SHA for branch $branch: $sha");

            // @TODO: Add a job to update web to the SHA
            // $contraint = new Constraint('=',  "dev-master#{$sha}");
            // $event->getRequest()->update('va-gov/web',$contraint);
    }

    /**
     * @param $string
     */
    static function printLine($string) {
        print self::CLI_PREFIX . $string . "\n";
    }
}


