<?php

namespace tests\phpunit\va_gov_content_release\functional\Form\Resolver;

use Tests\Support\Classes\VaGovBrowserTestBase;

/**
 * A deeeeeep test of the Entity Event Subscriber service.
 *
 * This is intended to test the full internal "on-demand" content release
 * system. Unfortunately, any unit test equivalent of this is going to be
 * at least a little inadequate just because of the seemingly bottomless
 * weirdness of Drupal's internal systems.
 *
 * For instance, $node->original is only created and populated when a node
 * is saved in a form submission. We can programmatically create a node and
 * set the original, but that doesn't trigger the same code path as a form
 * submission. So, we have to create a node, save it, and then edit it using
 * the form system. This is a lot of work, but it's the only way to test
 * the actual code path that we're trying to test.
 *
 * Thank you, Drupal, for making this so easy.
 *
 * @group functional
 * @group all
 */
class EntityEventSubscriberAcidTest extends VaGovBrowserTestBase {

}
