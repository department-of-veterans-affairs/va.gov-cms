#!/usr/bin/env bash

# Prevent tee from masking build failures.
set -eo pipefail

# NOTE: If we used the `va:web:build` command directly, it would throw a false
# positive if it were to exit 0. Instead of presuming that it will always exit 0
# if unable to read Drupal content, we make sure to check for the error as well.
#
# So, we decided to keep the "grep" for the Metalsmith error message to ensure
# that we catch the situation where the error is shown, but the command exits
# successfully (exit 0).
TEMPFILE=$( mktemp )
composer va:web:build | tee ${TEMPFILE}
grep "Failed to pipe Drupal content into Metalsmith!" -B1000 -C8 ${TEMPFILE} &&
  echo "tests.yml | composer va:web:build included the Drupal/Metalsmith error." &&
  exit 1 ||
    echo "tests.yml | Front end site was built! Check $DRUPAL_ADDRESS/static for raw output!"

exit $?
