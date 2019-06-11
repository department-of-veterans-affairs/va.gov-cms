<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\Process\Process

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

    /**
    * Initializes context.
    *
    * Every scenario gets its own context instance.
    * You can also pass arbitrary arguments to the
    * context constructor through behat.yml.
    */
    public function __construct() {

    }

    /**
     * @TODO: Move to provision-ops/behat-context (or whatever it becomes)
     * @Given I run :arg1
     */
    public function iRun($arg1)
    {

        // Set PATH to the repo composer bin-path.
        $_SERVER['PATH'] = realpath(dirname(dirname(dirname(dirname(__DIR__))))) . '/bin:' . $_SERVER['PATH'];

        // Prepare a Symfony process object.
        $process = new Process($arg1);
        $process->setEnv($_SERVER);
        $process->setTimeout(null);
        $process->mustRun(function ($code, $output) {
            print $output;
        });
    }

}
