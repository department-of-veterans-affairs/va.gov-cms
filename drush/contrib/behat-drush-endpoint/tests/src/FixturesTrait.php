<?php

namespace TestUtils;

use Drush\TestTraits\DrushTestTrait;

/**
 * Convenience class for creating fixtures.
 */
trait FixturesTrait
{
    use DrushTestTrait;

    protected function fixtures()
    {
        return Fixtures::instance();
    }

}
