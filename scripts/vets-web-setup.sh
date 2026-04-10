#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Installs & builds vets-website dependencies for next-build preview.
git config pull.rebase true

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." &> /dev/null && pwd)"

if [ ! -d vets-website ]; then
  git clone --filter=tree:0 https://github.com/department-of-veterans-affairs/vets-website.git vets-website
  cd vets-website
else
  cd vets-website
  echo "Repo vets-website already cloned. Updating..."
  git pull origin $(git rev-parse --abbrev-ref HEAD)
fi

nvm install && nvm use
npm install -g yarn

echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

export NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt
yarn install-safe
yarn build

# Gather vets-website assets based on build type
cd "$REPO_ROOT"

BUILD_TYPE="tugboat"
PROD_BUCKET="http://prod-va-gov-assets.s3-us-gov-west-1.amazonaws.com"
STAGING_BUCKET="http://staging-va-gov-assets.s3-us-gov-west-1.amazonaws.com"
DEV_BUCKET="http://dev-va-gov-assets.s3-us-gov-west-1.amazonaws.com"
LOCAL_BUCKET="$REPO_ROOT/vets-website/build/localhost/generated"

# Determine bucket based on build type
case "$BUILD_TYPE" in
  localhost)
    BUCKET="$LOCAL_BUCKET"
    ;;
  tugboat|vagovdev)
    BUCKET="$DEV_BUCKET"
    ;;
  vagovstaging)
    BUCKET="$STAGING_BUCKET"
    ;;
  vagovprod|*)
    BUCKET="$PROD_BUCKET"
    ;;
esac

FILE_MANIFEST_PATH="generated/file-manifest.json"
VETS_WEBSITE_ASSET_PATH="$REPO_ROOT/vets-website/src/site/assets"
DESTINATION_PATH="$REPO_ROOT/next/public/generated"

echo "Gathering vets-website assets from $BUILD_TYPE build..."

# Clean any existing assets or symlinks
if [ -d "$DESTINATION_PATH" ]; then
  echo "Removing existing vets-website assets..."
  rm -rf "$DESTINATION_PATH"
fi

# Handle asset gathering based on build type

echo "Downloading assets from $BUCKET..."
mkdir -p "$DESTINATION_PATH"

# Fetch manifest and download all assets
if ! MANIFEST=$(curl -s "$BUCKET/$FILE_MANIFEST_PATH"); then
  echo "Error: Failed to fetch file manifest from $BUCKET"
  exit 1
fi

# Parse JSON values
echo "$MANIFEST" | jq -r '.[]' | while read -r bundleFileName; do
BUNDLE_URL="${BUCKET}${bundleFileName}"

# Remove leading slash if present
BUNDLE_FILE="${bundleFileName#/}"
BUNDLE_PATH="$REPO_ROOT/next/public/$BUNDLE_FILE"

echo "Downloading: $BUNDLE_URL to $BUNDLE_PATH"
mkdir -p "$(dirname "$BUNDLE_PATH")"
if ! curl -s -f -o "$BUNDLE_PATH" "$BUNDLE_URL"; then
  echo "Warning: Failed to download $BUNDLE_URL"
fi
done

# Move additional assets (images and fonts) from vets-website
echo "Copying additional assets from vets-website..."
if [ -d "$VETS_WEBSITE_ASSET_PATH/img" ]; then
  cp -r "$VETS_WEBSITE_ASSET_PATH/img" "$REPO_ROOT/public/" && echo "Copied image assets."
fi

if [ -d "$VETS_WEBSITE_ASSET_PATH/fonts" ]; then
  mkdir -p "$DESTINATION_PATH"
  for font in "$VETS_WEBSITE_ASSET_PATH/fonts"/*; do
    cp -r "$font" "$DESTINATION_PATH/" && echo "Copied font: $(basename "$font")"
  done
fi

echo "All vets-website assets gathered successfully!"
