#!/usr/bin/env bash

# The purpose of this file is to create a number of malformed and/or poorly
# written files in the various programming/style/markup/etc languages that
# we use on a regular basis.
#
# This is so that when we can quickly verify that problematic code will trigger
# an appropriate response when testing a new or modified GitHub Action or other 
# test.
#
# We don't want to be in the situation where we're making random modifications
# to PHP files, injecting intentionally awful code, and then hoping that we
# remembered to undo those changes at merge time :)
#
# Instead, we create NEW files, with names that hopefully jar our senses enough
# that they don't just slip past.
#
# Note that common sense should still be exercised here, e.g. do not introduce 
# security vulnerabilities.  If one gets past QA, it's a very real problem.
# 
# These files should all be removed by executing the companion script in this
# directory.

# Change to the root of the repository, since we'll use relative paths.
pushd "$(git rev-parse --show-toplevel)" > /dev/null

# The Bad Place™.
directory=./docroot/modules/custom/BAD_TEST_MODULE;

# Create the BAD TEST MODULE.
#
# -p      don't fail if this directory already exists
mkdir -p ./docroot/modules/custom/BAD_TEST_MODULE

# Create some subdirectories to contain various types of files.
mkdir -p ./docroot/modules/custom/BAD_TEST_MODULE/js

# Create a .module file that commits various obvious sins.
#
# These should be caught by PHPStan, PHP_CodeSniffer, etc.
cat << 'EOF' > "${directory}/BAD_TEST_MODULE.module"
<?php

/**
 * @file
 * BAD_TEST_MODULE
 */

use Some\Namespace\That\Is\Not\And\Should\Never\Be;

EOF

# Create a bad .yml file.
cat << 'EOF' > "${directory}/BAD_TEST_MODULE.info.yml"
name: 'Just a single open quote.  That's all.
type: {'broken JSON'}
EOF

# Create a bad .js file.
cat << 'EOF' > "${directory}/js/BAD_TEST_MODULE.es6.js"
var results = [0,1,2,3].reduce((object, index) => {
  This won't work.
});
EOF

# Change back to the directory whence we came, whatever that might be.
popd > /dev/null
