#!/bin/bash -l

cd "${TUGBOAT_ROOT}"
[ -f "./docroot/sites/default/files/next-buildrequest.txt" ] && ./scripts/next-build-frontend.sh
sleep 10s
