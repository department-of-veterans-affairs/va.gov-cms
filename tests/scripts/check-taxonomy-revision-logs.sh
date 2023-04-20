#!/usr/bin/env bash

set -ex

export failure=0


for filename in config/sync/core.entity_form_display.taxonomy_term.*.default.yml; do
  if ! grep --quiet '\- revision_log_message' "${filename}"; then
    failure=1
    echo "The revision_log_message field was not found in ${filename}."
  fi
done

if [ "${failure}" -eq 1 ]; then
  echo "To fix this test, ensure that all taxonomy_term types"
  echo "have the revision_log_message present."
  exit 1
fi

exit 0
