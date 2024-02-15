#!/bin/bash -l

ROOT=${TUGBOAT_ROOT:-${DDEV_APPROOT:-/var/www/html}}
cd "${ROOT}"
[ -f "./docroot/sites/default/files/next-buildrequest.txt" ] && ./scripts/next-build-frontend.sh
sleep 10s
