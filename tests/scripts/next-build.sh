#!/usr/bin/env bash
# Tests Next Build's use of JSON:API and the Decoupled Router.
#
# Usage: next-build.sh [env-name]
#   env-name: Optional environment name (e.g., tugboat, dev, staging, prod)
#             Maps to next/envs/.env.{env-name}
#             If not provided, auto-detects from CMS_ENVIRONMENT_TYPE

set -eo pipefail

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

repo_root="$(git rev-parse --show-toplevel)"
# shellcheck source=/dev/null
source "${repo_root}/.env"

# Determine APP_ENV: use argument if provided, otherwise auto-detect from CMS_ENVIRONMENT_TYPE
if [ -n "${1:-}" ]; then
  if [ "$1" = "local" ]; then
    APP_ENV="example"
  else
    APP_ENV="$1"
  fi
else
  # Auto-detect based on CMS_ENVIRONMENT_TYPE
  case "${CMS_ENVIRONMENT_TYPE:-}" in
    tugboat) APP_ENV="tugboat" ;;
    prod)    APP_ENV="prod" ;;
    staging) APP_ENV="staging" ;;
    dev)     APP_ENV="dev" ;;
    *)       APP_ENV="" ;;  # Fall back to env-loader defaults
  esac
fi

if [ -n "${APP_ENV}" ]; then
  echo "Using APP_ENV: ${APP_ENV}"
fi

DRUPAL_ADDRESS="${DRUPAL_ADDRESS:-${DRUSH_OPTIONS_URI:-http://localhost}}"
FAILURES=0

# Generic HTTP test helper - returns body on stdout, exits non-zero on failure
http_get() {
  local url="$1"
  local response body http_code
  response=$(curl -gsS -w '\n%{http_code}' "$url" 2>&1) || return 1
  http_code="${response##*$'\n'}"
  body="${response%$'\n'*}"

  if [[ "$http_code" != 200 ]]; then
    echo "HTTP $http_code: $url" >&2
    return 1
  fi
  printf '%s' "$body"
}

run_test() {
  local name="$1"; shift
  printf 'Testing: %s... ' "$name"
  set +e
  out=$("$@" 2>&1)
  exit_code=$?
  set -e
  if [ "$exit_code" = "0" ]; then
    echo "PASSED"
  else
    echo "FAILED: Test '$name' failed with output: $out"
    ((FAILURES++)) || true
  fi
}

# Test assertions
assert_json_path() {
  local body="$1" path="$2"
  jq -e "$path" <<<"$body" >/dev/null
}

router_get_uuid() {
  local path="$1"
  local body
  body=$(http_get "${DRUPAL_ADDRESS}/router/translate-path?path=$(jq -rn --arg p "$path" '$p|@uri')") || return 1
  assert_json_path "$body" '.jsonapi.individual' || return 1
  jq -r '.entity.uuid' <<<"$body"
}

router_has_uuid() {
  local uuid
  uuid=$(router_get_uuid "$1") || return 1
  [[ -n "$uuid" && "$uuid" != "null" ]]

  printf "$uuid... "
}

jsonapi_has_data() {
  local endpoint="$1"
  local body
  body=$(http_get "${DRUPAL_ADDRESS}${endpoint}") || return 1

  echo $body | jq -rj '.data[0].id'
  printf '... '

  assert_json_path "$body" '.data[0].id'
}

router_to_jsonapi_flow() {
  local path="$1" content_type="$2"
  local uuid jsonapi_body

  uuid=$(router_get_uuid "$path") || return 1
  [[ -n "$uuid" && "$uuid" != "null" ]] || return 1

  jsonapi_body=$(http_get "${DRUPAL_ADDRESS}/jsonapi/node/${content_type}/${uuid}") || return 1
  [[ "$(jq -r '.data.id' <<<"$jsonapi_body")" == "$uuid" ]]
}

next_build() (
  set -e

  cd ./next
  # silence nvm install stderr which outputs when the node version is already installed
  nvm install 2>/dev/null
  nvm use

  APP_ENV="${APP_ENV}" BUILD_OPTION=static yarn export --no-USE_REDIS
)

echo "Testing against: ${DRUPAL_ADDRESS}"
echo ""

# Router tests
run_test "Router: VAMC system page"        router_has_uuid "/boston-health-care"
run_test "Router: Event listing"           router_has_uuid "/boston-health-care/events"
run_test "Router: Story listing"           router_has_uuid "/boston-health-care/stories"
run_test "Router: Locations listing"       router_has_uuid "/boston-health-care/locations"

# JSON:API tests
run_test "JSON:API: VAMC system nodes"     jsonapi_has_data "/jsonapi/node/health_care_region_page?page[limit]=1"
run_test "JSON:API: Event listing nodes"   jsonapi_has_data "/jsonapi/node/event_listing?page[limit]=1"
run_test "JSON:API: Story listing nodes"   jsonapi_has_data "/jsonapi/node/story_listing?page[limit]=1"

# Full flow tests
run_test "Flow: VAMC system"               router_to_jsonapi_flow "/boston-health-care" "health_care_region_page"
run_test "Flow: Event listing"             router_to_jsonapi_flow "/boston-health-care/events" "event_listing"

# Next-build test (ensure next-build is not broken in this environment)
run_test "Build: next-build"               next_build

echo ""
echo "Failures: $FAILURES"

exit $((FAILURES > 0))
