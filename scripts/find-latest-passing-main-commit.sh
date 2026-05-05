#!/usr/bin/env bash
set -euo pipefail

# Finds the newest mainline commit on main where
# required VA checks are present and passing. Outputs only the commit SHA to stdout.
# It searches the first-parent chain since the latest release tag first, then falls back to full first-parent main history.
# No tags/releases are created.

MAX_COMMITS=0
FALLBACK_MAX_COMMITS=""
REPO="${REPO:-}"
VERBOSE=0
JSON_MODE=0
JSON_PRETTY=0
MAX_REASON_SAMPLES=20
REQUIRED_CHECKS_JSON='["va/tests/cypress","va/tests/phpunit","va/tests/status-error","va/tests/content-build-gql"]'

usage() {
  cat <<'EOF'
Usage: find-latest-passing-main-commit.sh [--repo owner/name] [--max-commits N] [--fallback-max-commits N] [--verbose] [--json] [--json-pretty]

Options:
  --repo owner/name   GitHub repository (defaults to current gh repo)
  --max-commits N     Number of commits to inspect (default: 0 = all since last tag)
  --fallback-max-commits N  Cap for fallback full-main scan (default: same as --max-commits)
  --verbose           Print per-commit diagnostics to stderr
  --json              Print JSON output with selected SHA and skip summary
  --json-pretty       Same as --json, but pretty-printed
  -h, --help          Show this help

Environment:
  REPO                Same as --repo

Output:
  Default: prints one commit SHA to stdout when found.
  --json: prints a JSON object with selected_sha and skipped_summary.
  --json-pretty: same schema as --json, but indented.
  Search order: latest_tag..main, then full main history if needed.
  Candidate commits are taken from main's first-parent chain only.
  Required checks: va/tests/cypress, va/tests/phpunit,
                   va/tests/status-error, va/tests/content-build-gql.
  Exits non-zero if no qualifying commit is found.
EOF
}

log() {
  printf '%s\n' "$*" >&2
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --repo)
      if [[ $# -lt 2 || "${2-}" == -* ]]; then
        log "Missing value for --repo (expected: owner/name)"
        usage
        exit 2
      fi
      REPO="$2"
      shift 2
      ;;
    --max-commits)
      if [[ $# -lt 2 || "${2-}" == -* ]]; then
        log "Missing value for --max-commits (expected: non-negative integer)"
        usage
        exit 2
      fi
      MAX_COMMITS="$2"
      shift 2
      ;;
    --fallback-max-commits)
      if [[ $# -lt 2 || "${2-}" == -* ]]; then
        log "Missing value for --fallback-max-commits (expected: non-negative integer)"
        usage
        exit 2
      fi
      FALLBACK_MAX_COMMITS="$2"
      shift 2
      ;;
    --verbose)
      VERBOSE=1
      shift
      ;;
    --json)
      JSON_MODE=1
      shift
      ;;
    --json-pretty)
      JSON_MODE=1
      JSON_PRETTY=1
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      log "Unknown argument: $1"
      usage
      exit 2
      ;;
  esac
done

for cmd in git gh jq; do
  if ! command -v "$cmd" >/dev/null 2>&1; then
    log "Missing required command: $cmd"
    exit 2
  fi
done

if ! [[ "$MAX_COMMITS" =~ ^[0-9]+$ ]]; then
  log "--max-commits must be a non-negative integer"
  exit 2
fi

if [[ -n "$FALLBACK_MAX_COMMITS" ]] && ! [[ "$FALLBACK_MAX_COMMITS" =~ ^[0-9]+$ ]]; then
  log "--fallback-max-commits must be a non-negative integer"
  exit 2
fi

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  log "This script must run inside a git repository."
  exit 2
fi

if [[ -z "$REPO" ]]; then
  REPO=$(gh repo view --json nameWithOwner --jq '.nameWithOwner' 2>/dev/null || true)
fi
if [[ -z "$REPO" ]]; then
  log "Unable to determine repository. Provide --repo owner/name or set REPO."
  exit 2
fi

if ! gh auth status >/dev/null 2>&1; then
  log "GitHub CLI is not authenticated. Run: gh auth login"
  exit 2
fi

JQ_JSON_FLAGS=(-n -c)
if [[ "$JSON_PRETTY" -eq 1 ]]; then
  JQ_JSON_FLAGS=(-n)
fi

LATEST_TAG=$(git tag --list 'v[0-9]*.[0-9]*.[0-9]*' --sort=-version:refname | head -n1)
if [[ -n "$LATEST_TAG" ]]; then
  RANGES=("${LATEST_TAG}..main" "main")
  [[ "$VERBOSE" -eq 1 ]] && log "Searching range first: ${LATEST_TAG}..main"
else
  RANGES=("main")
  [[ "$VERBOSE" -eq 1 ]] && log "No release tags found; searching all commits on main."
fi

SEARCHED_RANGES=()

INSPECTED=0
SKIPPED_TOTAL=0
SKIPPED_MISSING_REQUIRED=0
SKIPPED_NOT_PASSING_REQUIRED=0
SAMPLED_REASONS=()

for RANGE in "${RANGES[@]}"; do
  SEARCHED_RANGES+=("$RANGE")

  EFFECTIVE_MAX_COMMITS="$MAX_COMMITS"
  # Apply a separate cap when scanning full main as a fallback path.
  if [[ "$RANGE" == "main" && -n "$LATEST_TAG" && -n "$FALLBACK_MAX_COMMITS" ]]; then
    EFFECTIVE_MAX_COMMITS="$FALLBACK_MAX_COMMITS"
  fi

  if [[ "$EFFECTIVE_MAX_COMMITS" -eq 0 ]]; then
    CANDIDATES=$(git log --first-parent --format='%H' "$RANGE" || true)
  else
    CANDIDATES=$(git log --first-parent --format='%H' -n "$EFFECTIVE_MAX_COMMITS" "$RANGE" || true)
  fi

  if [[ -z "$CANDIDATES" ]]; then
    [[ "$VERBOSE" -eq 1 ]] && log "No candidate commits found in range: $RANGE"
    continue
  fi

  CANDIDATE_COUNT=$(printf '%s\n' "$CANDIDATES" | sed '/^$/d' | wc -l | tr -d ' ')
  if [[ "$VERBOSE" -eq 1 ]]; then
    if [[ "$EFFECTIVE_MAX_COMMITS" -eq 0 ]]; then
      log "Evaluating range: $RANGE (candidates=$CANDIDATE_COUNT, max_commits=unlimited)"
    else
      log "Evaluating range: $RANGE (candidates=$CANDIDATE_COUNT, max_commits=$EFFECTIVE_MAX_COMMITS)"
    fi
  fi

  for SHA in $CANDIDATES; do
    INSPECTED=$((INSPECTED + 1))
    [[ "$VERBOSE" -eq 1 ]] && log "Checking $SHA"

    STATUS_RESPONSE=$(gh api "repos/${REPO}/commits/${SHA}/status")

    CHECK_SUMMARY=$(printf '%s\n' "$STATUS_RESPONSE" | jq --argjson required "$REQUIRED_CHECKS_JSON" '
    .statuses
    | sort_by(.updated_at // .created_at // "")
    | reduce .[] as $s ({}; .[$s.context] = $s)
    | [to_entries[].value | select(.context as $c | $required | index($c))]
    | {
        missing: ($required - (map(.context))),
        failing: [
          .[]
          | select(.state != "success")
          | "\(.context)=\(.state // "none")"
        ]
      }
  ')
    MISSING_COUNT=$(printf '%s\n' "$CHECK_SUMMARY" | jq -r '.missing | length')
    FAILING_COUNT=$(printf '%s\n' "$CHECK_SUMMARY" | jq -r '.failing | length')

    if [[ "$MISSING_COUNT" -gt 0 ]]; then
      SKIPPED_TOTAL=$((SKIPPED_TOTAL + 1))
      SKIPPED_MISSING_REQUIRED=$((SKIPPED_MISSING_REQUIRED + 1))
      MISSING_CHECKS=$(printf '%s\n' "$CHECK_SUMMARY" | jq -r '.missing | join(", ")')
      if [[ "${#SAMPLED_REASONS[@]}" -lt "$MAX_REASON_SAMPLES" ]]; then
        SAMPLED_REASONS+=("$SHA: missing_required=$MISSING_CHECKS")
      fi
      [[ "$VERBOSE" -eq 1 ]] && log "Missing required checks: $MISSING_CHECKS"
      continue
    fi

    if [[ "$FAILING_COUNT" -eq 0 ]]; then
      if [[ "$JSON_MODE" -eq 1 ]]; then
        if [[ "${#SAMPLED_REASONS[@]}" -gt 0 ]]; then
          REASONS_JSON=$(printf '%s\n' "${SAMPLED_REASONS[@]}" | jq -R . | jq -s .)
        else
          REASONS_JSON='[]'
        fi

        jq "${JQ_JSON_FLAGS[@]}" \
          --arg selected_sha "$SHA" \
          --arg repo "$REPO" \
          --arg range "$RANGE" \
          --arg latest_tag "${LATEST_TAG:-}" \
          --argjson inspected "$INSPECTED" \
          --argjson skipped_total "$SKIPPED_TOTAL" \
          --argjson skipped_missing_required "$SKIPPED_MISSING_REQUIRED" \
          --argjson skipped_not_passing_required "$SKIPPED_NOT_PASSING_REQUIRED" \
          --argjson samples "$REASONS_JSON" \
          --argjson searched_ranges "$(printf '%s\n' "${SEARCHED_RANGES[@]}" | jq -R . | jq -s .)" \
          '{selected_sha: $selected_sha, repo: $repo, range: $range, searched_ranges: $searched_ranges, latest_tag: $latest_tag, inspected_commits: $inspected, skipped_summary: {total: $skipped_total, missing_required: $skipped_missing_required, not_passing_required: $skipped_not_passing_required, samples: $samples}}'
      else
        printf '%s\n' "$SHA"
      fi
      exit 0
    fi

    SKIPPED_TOTAL=$((SKIPPED_TOTAL + 1))
    SKIPPED_NOT_PASSING_REQUIRED=$((SKIPPED_NOT_PASSING_REQUIRED + 1))

    if [[ "$VERBOSE" -eq 1 ]]; then
      FAILING_CHECKS=$(printf '%s\n' "$CHECK_SUMMARY" | jq -r '.failing | join(", ")')
      log "Required checks not passing: $FAILING_CHECKS"
    fi

    if [[ "${#SAMPLED_REASONS[@]}" -lt "$MAX_REASON_SAMPLES" ]]; then
      FAILING_CHECKS=$(printf '%s\n' "$CHECK_SUMMARY" | jq -r '.failing | join(", ")')
      SAMPLED_REASONS+=("$SHA: $FAILING_CHECKS")
    fi
  done
done

log "No commits found with all 4 required checks passing in searched ranges: ${SEARCHED_RANGES[*]}"
if [[ "$JSON_MODE" -eq 1 ]]; then
  if [[ "${#SAMPLED_REASONS[@]}" -gt 0 ]]; then
    REASONS_JSON=$(printf '%s\n' "${SAMPLED_REASONS[@]}" | jq -R . | jq -s .)
  else
    REASONS_JSON='[]'
  fi

  jq "${JQ_JSON_FLAGS[@]}" \
    --arg repo "$REPO" \
    --arg range "${SEARCHED_RANGES[0]:-main}" \
    --arg latest_tag "${LATEST_TAG:-}" \
    --argjson inspected "$INSPECTED" \
    --argjson skipped_total "$SKIPPED_TOTAL" \
    --argjson skipped_missing_required "$SKIPPED_MISSING_REQUIRED" \
    --argjson skipped_not_passing_required "$SKIPPED_NOT_PASSING_REQUIRED" \
    --argjson samples "$REASONS_JSON" \
    --argjson searched_ranges "$(printf '%s\n' "${SEARCHED_RANGES[@]}" | jq -R . | jq -s .)" \
    '{selected_sha: null, repo: $repo, range: $range, searched_ranges: $searched_ranges, latest_tag: $latest_tag, error: "no_passing_commit", inspected_commits: $inspected, skipped_summary: {total: $skipped_total, missing_required: $skipped_missing_required, not_passing_required: $skipped_not_passing_required, samples: $samples}}'
fi
exit 1
