#!/usr/bin/env bash

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

web_path="./web"

env_example_path="${web_path}/.env.example"
env_path="${web_path}/.env"

if [ -f "${env_example_path}" ]; then
  cat "${env_example_path}" | grep -v 'DRUPAL_ADDRESS' > "${env_path}"
fi

if [ -n "${TUGBOAT_DEFAULT_SERVICE_URL}" ]; then
  echo "DRUPAL_ADDRESS=${TUGBOAT_DEFAULT_SERVICE_URL}" >> "${env_path}"
elif [ -n "${DDEV_PRIMARY_URL}" ]; then
  echo "DRUPAL_ADDRESS=${DDEV_PRIMARY_URL}" >> "${env_path}"
elif [ -n "${DRUPAL_ADDRESS}" ]; then
  echo "DRUPAL_ADDRESS=${DRUPAL_ADDRESS}" >> "${env_path}"
else
  echo "DRUPAL_ADDRESS=https://dev.cms.va.gov" >> "${env_path}"
fi

popd > /dev/null
