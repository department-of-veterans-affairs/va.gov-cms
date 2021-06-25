#!/usr/bin/env bash

# Defines a function that performs a frontend build and then executes it in
# parallel, with each job offset from the previous.  The important data points
# are then output as CSV.
#
# This uses GNU parallel, which is widely available if not already installed.
while getopts ":c" opt; do
  case "${opt}" in
    c ) # Cold cache/clear cache before the runs.
      cold_cache=1;
      ;;
    o ) # Offset successive builds.
      offset=1;
      ;;
    \? )
      echo "Usage $0 [-co]";
      exit -1;
      ;;
  esac;
done;

: "${cold_cache:=0}";
: "${offset:=0}";

do_web_build() {
  build_response=$(yarn build:content \
    --drupal-address=https://test.staging.cms.va.gov \
    --pull-drupal \
    --no-drupal-proxy \
    --buildtype=vagovdev \
    --api=https://dev-api.va.gov \
    --asset-source=$(git rev-parse --verify HEAD));
  graphql_time=$(printf "%s" "${build_response}" | grep -oP 'queries in \d+s' | grep -oP '\d+');
  graphql_pages=$(printf "%s" "${build_response}" | grep -oP 'with \d+ pages' | grep -oP '\d+');
  build_time=$(printf "%s" "${build_response}" | grep -oP 'Done in \d+\.\d+s' | grep -oP '\d+\.\d+');
  echo "\"${i}\",\"${graphql_time}\",\"${graphql_pages}\",\"${build_time}\"";
}
export -f do_web_build;

if [ "${cold_cache}" -eq 1 ]; then
  drush cr > /dev/null;
fi;

echo '
do_web_build
([ "${offset}" -ne 1 ] || sleep 5) && do_web_build
([ "${offset}" -ne 1 ] || sleep 10) && do_web_build
([ "${offset}" -ne 1 ] || sleep 15) && do_web_build
([ "${offset}" -ne 1 ] || sleep 20) && do_web_build
' | parallel;
