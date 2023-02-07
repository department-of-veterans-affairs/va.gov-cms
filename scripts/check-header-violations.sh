#!/usr/bin/env bash

set -xe;

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

pushd web

yarn list-heading-order-violations

cp heading_order_violations.html /var/lib/tugboat/docroot/

curl -X POST "https://api.ddog-gov.com/api/v1/series" \
  -H "Content-Type: text/json" \
  -H "DD-API-KEY: ${HA_HA_HA_NO_DATADOG_KEY_YET}" \
  -d @- < heading_order_violations.json

popd

popd > /dev/null