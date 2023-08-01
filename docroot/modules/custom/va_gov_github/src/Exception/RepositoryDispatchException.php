<?php

namespace Drupal\va_gov_github\Exception;

/**
 * We could not create a repository dispatch event.
 *
 * This is generally a serious issue in practice, because it
 * means content releases will not be dispatched, etc.
 */
class RepositoryDispatchException extends \Exception {}
