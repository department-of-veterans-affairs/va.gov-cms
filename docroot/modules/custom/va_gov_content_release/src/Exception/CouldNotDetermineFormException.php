<?php

namespace Drupal\va_gov_content_release\Exception;

/**
 * We could not determine a valid content release form.
 *
 * This would normally be the result of an environment that does not map to a
 * valid form.
 */
class CouldNotDetermineFormException extends \Exception {}
