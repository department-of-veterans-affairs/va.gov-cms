#!/bin/bash

bail_if_test_failed () {
  if [ $? -ne 0 ]; then
    echo $@;
    exit 1;
  fi
}

repo_root="$(git rev-parse --show-toplevel)";
pushd "${repo_root}" > /dev/null;

# List changes to be committed, excluding deleted files.
CHANGES=$( git diff --diff-filter=d --name-only HEAD );

PHP_FILES=$( echo "${CHANGES}" | grep -E '\.(php|module|inc|install|profile|engine|theme|css)$' );
if [ "${#PHP_FILES}" -gt 0 ]; then
  composer va:test:php_codesniffer -- ${PHP_FILES[*]};
  bail_if_test_failed;
fi;

# Install npm modules if node_modules is missing or empty.
if [ ! -d "./node_modules" ]; then
  npm install;
fi;

JS_FILES=$( echo "${CHANGES}" | grep -E '\.es6.js$' );
if [ "${#JS_FILES}" -gt 0 ]; then
  composer va:test:eslint -- ${JS_FILES[*]};
  bail_if_test_failed;
fi;

CSS_FILES=$( echo "${CHANGES}" | grep -E 'docroot/modules/custom.*\.css$' );
if [ "${#CSS_FILES}" -gt 0 ]; then
  composer va:test:stylelint-modules -- ${CSS_FILES[*]};
  bail_if_test_failed;
fi;

SCSS_FILES=$( echo "${CHANGES}" | grep -E 'docroot/themes/custom.*\.scss$' );
if [ "${#SCSS_FILES}" -gt 0 ]; then
  composer va:test:stylelint-themes -- ${SCSS_FILES[*]};
  bail_if_test_failed;
fi;

SERVICES_ROOT=./docroot/sites/default/services;
SERVICES_FILE1="$SERVICES_ROOT/services.staging.yml";
SERVICES_FILE2="$SERVICES_ROOT/services.prod.yml";
diff <(yq -P "$SERVICES_FILE1") <(yq -P "$SERVICES_FILE2");
bail_if_test_failed "Mismatch in ${SERVICES_FILE1} and ${SERVICES_FILE2}; these files should always remain the same.";

exit 0;

popd > /dev/null;
