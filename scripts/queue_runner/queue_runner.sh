#!/bin/bash -l

cd "${TUGBOAT_ROOT}"
./bin/drush advancedqueue:queue:process command_runner 2>&1
./bin/drush advancedqueue:queue:process content_release 2>&1
[ -f "./docroot/sites/default/files/.buildrequest" ] && ./scripts/build-frontend.sh && rm ./docroot/sites/default/files/.buildrequest
#[ -f "./docroot/sites/default/files/.next-buildrequest" ] && ./scripts/next-build-frontend.sh && rm ./docroot/sites/default/files/.next-buildrequest
sleep 60s
