#!/bin/bash

# Start lando if it's not already running.
LANDO_SERVICE_COUNT=$( lando list --format json | jq '.[].status' | wc -l )
if [[ ${LANDO_SERVICE_COUNT} -lt 4 ]]; then
  lando start
fi
