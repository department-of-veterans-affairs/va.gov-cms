<?php

namespace Drupal\va_gov_github\Exception;

/**
 * We could not retrieve a GitHub API token.
 *
 * In some environments, this is a serious issue, and will prevent content
 * release.
 *
 * In local environments, this is an expected, default behavior. But we
 * should raise it to the user, so they know they need to set a token if
 * these operations are relevant to their work.
 */
class NonexistentApiTokenException extends \Exception {}
