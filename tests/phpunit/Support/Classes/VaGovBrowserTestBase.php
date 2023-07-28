<?php

namespace Tests\Support\Classes;

use Prophecy\PhpUnit\ProphecyTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Common functionality for BrowserTestBase derivative test classes.
 */
abstract class VaGovBrowserTestBase extends BrowserTestBase {

  use ProphecyTrait;

}
