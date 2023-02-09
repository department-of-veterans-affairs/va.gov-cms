#!/bin/bash

bail_if_test_failed () {
  if [ $? -ne 0 ]
  then
    echo $@
    exit 1
  fi
}

CHANGES=$( git diff --diff-filter=d --name-only HEAD )

PHP_FILES=$( echo "${CHANGES}" | grep -E '\.(php|module|inc|install|profile|engine|theme|css)$' )
if [ ${#PHP_FILES} -gt 0 ]; then
  phpcs --colors ${PHP_FILES[*]}
  bail_if_test_failed
fi

# Install npm modules if node_modules is missing or empty.
if ! find node_modules -mindepth 1 2> /dev/null| read
then
  npm install
fi

JS_FILES=$( echo "${CHANGES}" | grep -E '\.es6.js$' )
if [ ${#JS_FILES} -gt 0 ]; then
  npm run eslint ${JS_FILES[*]}
  bail_if_test_failed
fi

CSS_FILES=$( echo "${CHANGES}" | grep -E 'docroot/modules/custom.*\.css$' )
if [ ${#CSS_FILES} -gt 0 ]; then
  npm run stylelint ${CSS_FILES[*]}
  bail_if_test_failed
fi

SCSS_FILES=$( echo "${CHANGES}" | grep -E 'docroot/themes/custom.*\.scss$' )
if [ ${#SCSS_FILES} -gt 0 ]; then
  npm run stylelint ${SCSS_FILES[*]}
  bail_if_test_failed
fi

SERVICES_ROOT=docroot/sites/default/services
SERVICES_FILE1="$SERVICES_ROOT/services.staging.yml"
SERVICES_FILE2="$SERVICES_ROOT/services.prod.yml"
diff <(yq -P "$SERVICES_FILE1") <(yq -P "$SERVICES_FILE2")
bail_if_test_failed "Mismatch in ${SERVICES_FILE1} and ${SERVICES_FILE2}; these files should always remain the same."

exit 0
