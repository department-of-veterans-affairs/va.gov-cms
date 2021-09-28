#! /usr/bin/env bash

set -e

# This script updates the Facilities endpoint monitoring test with the current list of facilities.
# When making changes to this script, copy the test in the UI and change TEST_ID and test there first.
#
# USAGE:
#     Export below variables locally, then run: `./datadog-update-facilities-test.sh`.    Uncomment `nuke_tests` and comment out `create_tests` at the end of the script to make changes.
#
# Retrieve from https://app.datadoghq.com/account/settings#api
# `export DD_API_KEY="XXXXXXXXXXXX"`
#
# Retrieve from https://app.datadoghq.com/access/application-keys
# `export DD_APP_KEY="XXXXXXXXXXXX"`
#
#
# Testing notes: If you need to export a new test, just curl it and get the JSON structure that way.

TAG_FILTER="test_cms_facilities_test"


nuke_tests() {
  get_existing_tests

  # TODO IF NOT EMPTY
  # Nuke them all. Should really only be run manually for testing.
  curl -X POST "https://api.datadoghq.com/api/v1/synthetics/tests/delete" \
  -H "Content-Type: application/json" \
  -H "DD-API-KEY: $DD_API_KEY" \
  -H "DD-APPLICATION-KEY: $DD_APP_KEY" \
  -d @- <<EOF
    $EXISTING_TEST_IDS
EOF

  # TODO Improve - prefix with newline
  echo "Deleted $EXISTING_TEST_IDS"
}

get_existing_tests() {
  EXISTING_TESTS=$(curl \
    --silent \
    --request GET "https://api.datadoghq.com/api/v1/synthetics/tests" \
    --header "Content-Type: application/json" \
    --header "DD-API-KEY: $DD_API_KEY" \
    --header "DD-APPLICATION-KEY: $DD_APP_KEY"
  )

  EXISTING_TEST_IDS=$(echo "$EXISTING_TESTS" | jq "public_ids: [.tests[] | select(.tags[] | test('${TAG_FILTER}')).public_id]}")
}

# GET current facility status endpoints URLs from:
# https://raw.githubusercontent.com/department-of-veterans-affairs/va.gov-cms/master/config/sync/migrate_plus.migration.va_node_health_care_local_facility_status.yml
get_facility_status_urls() {
  FACILITY_STATUS_URLS=$( \
    curl \
      --silent \
      --request GET https://raw.githubusercontent.com/department-of-veterans-affairs/va.gov-cms/master/config/sync/migrate_plus.migration.va_node_health_care_local_facility_status.yml | \
    grep "\- '.*'"  | \
    tr --delete '-' | \
    tr --delete "'"
  )
}

create_tests() {
  get_facility_status_urls
  for URL in $FACILITY_STATUS_URLS; do
    POST_BODY=$(jq --arg TEST_NAME "[CMS] Facility Status: $URL" --arg FACILITY_STATUS_URL "$URL" --arg TAG_NAME ${TAG_FILTER} --null-input --from-file test-template.jq)
#    echo $POST_BODY
#    echo $URL
    curl \
      --silent \
      --request POST "https://api.datadoghq.com/api/v1/synthetics/tests/api" \
      --header "Content-Type: application/json" \
      --header "DD-API-KEY: $DD_API_KEY" \
      --header "DD-APPLICATION-KEY: $DD_APP_KEY" \
      --data @- <<EOF
        $POST_BODY
EOF
  done
}

#create_tests
nuke_tests
