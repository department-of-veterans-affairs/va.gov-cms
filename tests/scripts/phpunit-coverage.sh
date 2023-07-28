#!/bin/bash

xdebug_enabled=$(php -m | grep xdebug | wc -l | tr -d '[:space:]');

if [ "$xdebug_enabled" -eq 0 ]; then
  enable_xdebug
  trap disable_xdebug ERR INT TERM
fi

export XDEBUG_MODE=coverage;

time phpunit \
  --group unit \
  --exclude-group disabled \
  --coverage-text \
  --coverage-html docroot/phpunit_coverage \
  tests/phpunit/

if [ "$xdebug_enabled" -eq 0 ]; then
  disable_xdebug
fi
