#!/bin/sh

cd "${TUGBOAT_ROOT}"
./bin/drush advancedqueue:queue:process command_runner 2>&1
sleep 60s
