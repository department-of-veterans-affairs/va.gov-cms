#!/usr/bin/env bash

# This script is used to build the project continuously.
# The project is build on GitHub using a Workflow.

# The continuous build only runs during business hours.
export TZ="America/New_York"
echo "Current time: $(date)"
if [ $(date +%u) -lt 6 ] && [ $(date +%H) -ge 8 ] && [ $(date +%H) -lt 20 ]; then
  echo "It is during business hours. Proceeding with the build."
else
  echo "It is not during business hours. Exiting build."
  exit 0
fi

# Make request to Github to check if a workflow is running.
# If a workflow is running, then exit the script.
# If no workflow is running, then build the project.
OWNER="department-of-veterans-affairs"
REPO="next-build"
WORKFLOW_ID="content_release.yml"

# Get the Content Release workflow status.
WORKFLOW_STATUS=$(curl -L \
                    -H "Accept: application/vnd.github+json" \
                    -H "X-GitHub-Api-Version: 2022-11-28" \
                    https://api.github.com/repos/$OWNER/$REPO/actions/workflows/$WORKFLOW_ID/runs | jq '.workflow_runs[0].status' | tr -d '"')
echo "Workflow status: ${WORKFLOW_STATUS}"

if [ "$WORKFLOW_STATUS" == "in_progress" ]; then
    echo "Workflow is running. Exiting the script."
    exit 0
else
    echo "Workflow is not running. Building the project."
fi

# Make call to trigger the workflow.
# The workflow is triggered by making a POST request to the GitHub API.
curl -L \
  -X POST \
  -H "Accept: application/vnd.github+json" \
  -H "Authorization: Bearer ${GH_TOKEN}" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  https://api.github.com/repos/$OWNER/$REPO/actions/workflows/$WORKFLOW_ID/dispatches \
  -d '{"ref":"main"}'

