#!/usr/bin/env bash
set -euo pipefail

SPEC_ROOT="${SPEC_ROOT:-tests/cypress/integration/features}"
SHARD_INDEX="${SHARD_INDEX:-0}"
SHARD_TOTAL="${SHARD_TOTAL:-1}"

if [[ -z "${SHARD_INDEX}" || -z "${SHARD_TOTAL}" ]]; then
  echo "SHARD_INDEX and SHARD_TOTAL must be set." >&2
  exit 1
fi

exec node scripts/cypress/run-sharded.js \
  --spec-root="${SPEC_ROOT}" \
  "$@"
