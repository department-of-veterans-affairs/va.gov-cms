<?php

namespace Drupal\va_gov_content_release\Exception;

/**
 * We could not determine a valid content release strategy.
 *
 * This would normally be the result of an environment that does not map to a
 * valid strategy.
 */
class CouldNotDetermineStrategyException extends \Exception {}
