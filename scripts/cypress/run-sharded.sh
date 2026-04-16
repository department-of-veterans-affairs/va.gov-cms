#!/usr/bin/env bash
set -euo pipefail

SPEC_ROOT="${SPEC_ROOT:-tests/cypress/integration/features}"
SHARD_INDEX="${SHARD_INDEX:-0}"
SHARD_TOTAL="${SHARD_TOTAL:-1}"

exec node scripts/cypress/run-sharded.js \
  --spec-root="${SPEC_ROOT}" \
  "$@"
