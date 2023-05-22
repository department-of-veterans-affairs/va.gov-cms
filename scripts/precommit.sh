#!/bin/bash

# Echo a message to stderr and exit with a failing exit code.
bail_if_test_failed () {
  if [ $? -ne 0 ]; then
    echo $@ >&2
    exit 1
  fi
}

# Always start in the directory root.
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

# List changes to be committed, excluding deleted files.
CHANGES=$( git diff --diff-filter=d --name-only HEAD )

# Validate that the Composer.lock file is up-to-date.
VALIDATE_OUTPUT="$(composer validate 2>&1)"
VALIDATED=$?
if [ "${VALIDATED}" -ne 0 ]; then
  echo "${VALIDATE_OUTPUT}"
  bail_if_test_failed
fi

# Use PHP_CodeSniffer to lint changed/added PHP files.
PHP_FILES=$( echo "${CHANGES}" | grep -E '\.(php|module|inc|install|profile|engine|theme|css)$' )
if [ "${#PHP_FILES}" -gt 0 ]; then
  composer va:test:php_codesniffer -- ${PHP_FILES[*]}
  bail_if_test_failed
fi

# Install npm modules if node_modules is missing or empty.
# This is required for the following set of tests.
if [ ! -d "./node_modules" ]; then
  npm install
fi

# Use ESLint to lint changed/added JS files.
# We normally write in ES6-conformant JS and cross-compile to a more compatible
# dialect, so we don't bother linting the generated JS.
JS_FILES=$( echo "${CHANGES}" | grep -E '^(.*\.es6.js|tests/cypress/.*.js)$' )
if [ ${#JS_FILES} -gt 0 ]; then
  composer va:test:eslint -- ${JS_FILES[*]}
  bail_if_test_failed
fi

# Use StyleLint to lint changed CSS files in the modules.
CSS_FILES=$( echo "${CHANGES}" | grep -E 'docroot/modules/custom.*\.css$' )
if [ "${#CSS_FILES}" -gt 0 ]; then
  composer va:test:stylelint-modules -- ${CSS_FILES[*]}
  bail_if_test_failed
fi

# Use StyleLint to lint changed SCSS files in the themes.
SCSS_FILES=$( echo "${CHANGES}" | grep -E 'docroot/themes/custom.*\.scss$' )
if [ "${#SCSS_FILES}" -gt 0 ]; then
  composer va:test:stylelint-themes -- ${SCSS_FILES[*]}
  bail_if_test_failed
fi

# Compare staging and prod services files; these should always be identical.
SERVICES_ROOT=./docroot/sites/default/services
SERVICES_FILE1="$SERVICES_ROOT/services.staging.yml"
SERVICES_FILE2="$SERVICES_ROOT/services.prod.yml"
diff <(yq -P "$SERVICES_FILE1") <(yq -P "$SERVICES_FILE2")
bail_if_test_failed "Mismatch in ${SERVICES_FILE1} and ${SERVICES_FILE2}; these files should always remain the same."

popd > /dev/null
