#!/usr/bin/env bash

# The purpose of this file is to quickly and safely remove the files introduced
# by the companion script, `create-bad-test-files.sh`.
#
# See that file for more information.

# Change to the root of the repository, since we'll use relative paths.
pushd "$(git rev-parse --show-toplevel)"

# Delete the BAD TEST MODULE.
# -r    recursively
# -f    don't confirm, and don't complain if the file doesn't exist
# -v    list files as they are removed
rm -rfv ./docroot/modules/custom/BAD_TEST_MODULE

# Change back to the directory whence we came, whatever that might be.
popd
