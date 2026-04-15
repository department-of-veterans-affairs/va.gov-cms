#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." &> /dev/null && pwd)"

# Gather vets-website assets
cd "$REPO_ROOT"

DEV_BUCKET="https://dev-va-gov-assets.s3-us-gov-west-1.amazonaws.com"

FILE_MANIFEST_PATH="generated/file-manifest.json"
DESTINATION_PATH="$REPO_ROOT/next-assets/public/generated"

echo "Gathering vets-website assets from DEV build..."

# Clean any existing assets or symlinks
if [ -d "$DESTINATION_PATH" ]; then
  echo "Removing existing vets-website assets..."
  rm -rf "$DESTINATION_PATH"
fi

# Handle asset gathering

echo "Downloading assets from $DEV_BUCKET..."
mkdir -p "$DESTINATION_PATH"

# Fetch manifest and download all assets
FULL_MANIFEST_URL="$DEV_BUCKET/$FILE_MANIFEST_PATH"
echo "DEBUG: Fetching manifest from: $FULL_MANIFEST_URL"
MANIFEST=$(curl -sf --compressed "$FULL_MANIFEST_URL" | tr -d '\0')
CURL_EXIT=$?
if [ $CURL_EXIT -ne 0 ]; then
  echo "Error: Failed to fetch file manifest from $DEV_BUCKET"
  echo "DEBUG: URL was: $FULL_MANIFEST_URL"
  echo "DEBUG: curl exit code: $CURL_EXIT"
  exit 1
fi

# Parse JSON values
echo "$MANIFEST" | jq -r '.[]' | while read -r bundleFileName; do
BUNDLE_URL="${DEV_BUCKET}${bundleFileName}"

# Remove leading slash if present
BUNDLE_FILE="${bundleFileName#/}"
BUNDLE_PATH="$REPO_ROOT/next-assets/public/$BUNDLE_FILE"

echo "Downloading: $BUNDLE_URL to $BUNDLE_PATH"
mkdir -p "$(dirname "$BUNDLE_PATH")"
if ! curl -s -f --compressed -o "$BUNDLE_PATH" "$BUNDLE_URL"; then
  echo "Warning: Failed to download $BUNDLE_URL"
fi
done

echo "All vets-website assets gathered successfully!"
