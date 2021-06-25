#!/usr/bin/env bash

# This script will run 5 sequential frontend builds, outputting the salient
# details as CSV at the end of each run.

while getopts ":c" opt; do
  case "${opt}" in
    c ) # Cold cache/clear cache before each run.
      cold_cache=1;
      ;;
    \? )
      echo "Usage $0 [-c]";
      exit -1;
      ;;
  esac;
done;

: "${cold_cache:=0}";

PATH=$PATH:/usr/local/bin:/var/www/cms/bin
export NODE_ENV=production;
export NODE_TLS_REJECT_UNAUTHORIZED=0;

for i in $(seq 1 5); do

  if [ "${cold_cache}" -eq 1 ]; then
    drush cr > /dev/null;
  fi;

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

done;
