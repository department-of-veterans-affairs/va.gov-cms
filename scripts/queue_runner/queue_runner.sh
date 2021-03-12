#!/bin/sh

cd "${TUGBOAT_ROOT}"
./bin/drupal advancedqueue:queue:process command_runner 2>&1
sleep 60s
