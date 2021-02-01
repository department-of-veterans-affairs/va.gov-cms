#!/bin/bash

bail_if_test_failed () {
  if [ $? -ne 0 ]
  then
    exit 1
  fi
}

CHANGES=$( git diff --diff-filter=d --name-only HEAD )

PHP_FILES=$( echo "${CHANGES}" | grep -E '\.(php|module|inc|install|profile|engine|theme|css)$' )
if [ ${#PHP_FILES} -gt 0 ]; then
  phpcs --ignore=*.md,*.min.css,styles.css,wysiwyg.css,proofing.css,user_guides.css,whats_new.css,*/node_modules/*,*/simplesaml*/* --extensions=php,module,inc,install,profile,engine,theme,css --standard=./docroot/vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml --colors ${PHP_FILES[*]}
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

exit 0
