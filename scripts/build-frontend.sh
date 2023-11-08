#!/usr/bin/env bash

# Exit if a command fails with a non-zero status code.
set -ex

# Find repo root -> $reporoot
reporoot="unknown"
if [ ! -z "$IS_DDEV_PROJECT" ]; then
  reporoot="/var/www/html"
fi
if [ ! -z "$TUGBOAT_ROOT" ]; then
  reporoot="$TUGBOAT_ROOT"
fi
if [ "$reporoot" == "unknown" ]; then
  echo "[!] Could not determine the environment type. Aborting!"
  exit 1
fi

# For convenience.
cd $reporoot

# Make sure that we're ready to start a build.
releasestate=$(drush va-gov:content-release:get-state | grep -c 'dispatched')
if [ "$releasestate" -ne 1 ]; then
  echo "[!] Build hasn't been requested by the site. Aborting!"
  exit 1
fi

# We really only want one build running at a time on any given environment.
if [ -f "${reporoot}/.buildlock" ]; then
  echo "[!] There is already a build in progress. Aborting!"
  exit 1
fi
touch ${reporoot}/.buildlock

# Make sure we clean up the build lock file if an error occurs or the build is killed.
trap "rm -f ${reporoot}/.buildlock" INT TERM EXIT

# Just because the path is really long:
logfile="${reporoot}/docroot/sites/default/files/build.txt"

# The currently selected version of content-build (may be "__default", a PR#, or a git ref)
content_build_version=$(drush va-gov-content-release:frontend-version:get content_build | tail -1)

# The currently selected version of vets-website (may be "__default", a PR#, or a git ref)
vets_website_version=$(drush va-gov-content-release:frontend-version:get vets_website | tail -1)

# Create a fresh log file.
[ -f "${logfile}" ] && rm ${logfile}
touch ${logfile}

date >> ${logfile}

echo "content-build version: ${content_build_version}" >> ${logfile}
echo "vets-website version: ${vets_website_version}" >> ${logfile}

# Tell the frontend (and the user) that we're starting.
drush va-gov:content-release:advance-state starting
echo "==> Starting a frontend build. This file will be updated as the build progresses." >> ${logfile}

# Reset the repos to defaults.
echo "==> Resetting VA repos to default versions" >> ${logfile}
rm -rf ${reporoot}/docroot/vendor/va-gov
composer install --no-scripts &>> ${logfile}

# Get the requested content-build version
if [ "${content_build_version}" != "__default" ]; then
  echo "==> Checking out the requested frontend version" >> ${logfile}
  pushd ${reporoot}/docroot/vendor/va-gov/content-build
  if echo "${content_build_version}" | grep -qE '^[0-9]+$' > /dev/null; then
    echo "==> Checking out PR #${content_build_version}"
    git fetch origin pull/${content_build_version}/head &>> ${logfile}
  else
    echo "==> Checking out git ref ${content_build_version}"
    git fetch origin ${content_build_version} &>> ${logfile}
  fi
  git checkout FETCH_HEAD &>> ${logfile}
  popd
else
  echo "==> Using default content-build version" >> ${logfile}
fi

# Install 3rd party deps.
echo "==> Installing yarn dependencies" >> ${logfile}
composer va:web:install &>> ${logfile}

# Get the requested vets-website version
if [ "${vets_website_version}" != "__default" ]; then
  echo "==> Checking out the requested vets-website version" >> ${logfile}
  pushd ${reporoot}/docroot/vendor/va-gov/vets-website
  if echo "$vets_website_version" | grep -qE '^[0-9]+$' > /dev/null; then
    echo "==> Checking out PR #${vets_website_version}"
    git fetch origin pull/${vets_website_version}/head &>> ${logfile}
  else
    echo "==> Checking out git ref ${vets_website_version}"
    git fetch origin ${vets_website_version} &>> ${logfile}
  fi
  git checkout FETCH_HEAD &>> ${logfile}
  popd
else
  echo "==> Using default vets-website version" >> ${logfile}
fi

# Run the build.
echo "==> Starting build" >> ${logfile}
drush va-gov:content-release:advance-state inprogress
composer va:web:build &>> ${logfile}

# Advance the state in the frontend so another build can start.
echo "==> Build complete" >> ${logfile}
drush va-gov:content-release:advance-state complete
drush va-gov:content-release:advance-state ready

# After this point, we are less concerned with errors; the build has completed.
set +e

echo "==> Broken link report" >> ${logfile}
broken_links_path="${reporoot}/docroot/vendor/va-gov/content-build/logs/vagovdev-broken-links.json"
cat "${broken_links_path}" | jq >> ${logfile}

echo "==> List heading order violations" >> ${logfile}
pushd ./web
PATH="${reporoot}/bin:$PATH"
yarn list-heading-order-violations 2>&1 | grep -vE '^Processing file ' &>> ${logfile}
cp -v heading_order_violations.html ${reporoot}/docroot/ &>> ${logfile}
curl -X POST "https://api.ddog-gov.com/api/v2/series" \
  -H "Content-Type: application/json" \
  -H "DD-API-KEY: ${CMS_DATADOG_API_KEY}" \
  -d @- < heading_order_violations.json &>> ${logfile}
popd

# Make sure other builds can start.
rm -f ${reporoot}/.buildlock

# Just in case it wasn't clear :)
echo "==> Done" >> ${logfile}
