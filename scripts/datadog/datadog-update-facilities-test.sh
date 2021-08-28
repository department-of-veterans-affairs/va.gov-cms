#! /usr/bin/env bash

set -e

# This script updates the Facilities endpoint monitoring test with the current list of facilities.
# When making changes to this script, copy the test in the UI and change TEST_ID and test there first.
#
# Usage: Export below variables then ./datadog-update-facilities-test.sh
#
# Retrieve from https://app.datadoghq.com/account/settings#api
# export DD_API_KEY="XXXXXXXXXXXX"
#
# Retrieve from https://app.datadoghq.com/access/application-keys
# export DD_APP_KEY="XXXXXXXXXXXX"

# https://app.datadoghq.com/synthetics/details/d2r-qjm-n4z (a 'test' test for now)
TEST_ID="d2r-qjm-n4z"

# GET current location endpoints from master branch on GitHub
# https://raw.githubusercontent.com/department-of-veterans-affairs/va.gov-cms/master/config/sync/migrate_plus.migration.va_node_health_care_local_facility_status.yml
LOCATION_URLS=$( \
  curl \
    --silent \
    --request GET https://raw.githubusercontent.com/department-of-veterans-affairs/va.gov-cms/master/config/sync/migrate_plus.migration.va_node_health_care_local_facility_status.yml | \
  grep "\- '.*'"  | \
  tr --delete '-' | \
  tr --delete "'"
)

# GET current test configuration so we can add the endpoints to it
# https://docs.datadoghq.com/api/latest/synthetics/#get-an-api-test
TEST_CONFIG=$( \
  curl \
    --request GET "https://api.datadoghq.com/api/v1/synthetics/tests/api/${TEST_ID}" \
    --header "Content-Type: application/json" \
    --header "DD-API-KEY: ${DD_API_KEY}" \
    --header "DD-APPLICATION-KEY: ${DD_APP_KEY}"
)

# Clear out the existing test steps
TEST_CONFIG=$( \
  echo "$TEST_CONFIG" | \
  jq 'del(.config.steps)' | \
  jq '.config += {"steps": []}'
)

## Loop through and add a new step for each location.
STEPS_FILE=$( mktemp )
for URL in $LOCATION_URLS; do
  jq --arg URL ${URL} --null-input --from-file step-template.jq >> ${STEPS_FILE}
done
TEST_CONFIG=$(
  echo ${TEST_CONFIG} | \
  jq --slurpfile steps ${STEPS_FILE} '.config.steps += $steps' \
)

# DEBUG
# echo $TEST_CONFIG | jq

## PUT the updated test
# https://docs.datadoghq.com/api/latest/synthetics/#edit-an-api-test
curl -v \
  --silent \
  --request PUT "https://api.datadoghq.com/api/v1/synthetics/tests/api/${TEST_ID}" \
  --header "Content-Type: application/json" \
  --header "DD-API-KEY: ${DD_API_KEY}" \
  --header "DD-APPLICATION-KEY: ${DD_APP_KEY}" \
  --data @- << EOF
    $TEST_CONFIG
EOF
