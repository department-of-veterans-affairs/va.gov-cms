#!/bin/sh

cd "${TUGBOAT_ROOT}"
./bin/drupal advancedqueue:queue:process command_runner
sleep 60s
