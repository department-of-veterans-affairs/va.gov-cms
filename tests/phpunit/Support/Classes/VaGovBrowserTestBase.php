<?php

namespace Tests\Support\Classes;

use Drupal\Tests\BrowserTestBase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Common functionality for BrowserTestBase derivative test classes.
 */
abstract class VaGovBrowserTestBase extends BrowserTestBase {

  use ProphecyTrait;

}
