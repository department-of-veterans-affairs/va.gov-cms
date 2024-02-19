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

# Store path to site default files directory.
filesdir="${reporoot}/docroot/sites/default/files"

# We really only want one build running at a time on any given environment.
if [ -f "${filesdir}/next-buildlock.txt" ]; then
  echo "[!] There is already a build in progress. Aborting!"
  exit 1
fi
touch ${filesdir}/next-buildlock.txt

# Make sure we clean up the build lock file if an error occurs or the build is killed.
trap "rm -f ${filesdir}/next-buildlock.txt && rm -f ${filesdir}/next-buildrequest.txt" INT TERM EXIT

# Just because the path is really long:
logfile="${filesdir}/next-build.txt"

# The currently selected version of next-build (may be "__default", a PR#, or a git ref)
next_build_version=$(drush va-gov-content-release:frontend-version:get next_build | tail -1)

# The currently selected version of vets-website (may be "__default", a PR#, or a git ref)
vets_website_version=$(drush va-gov-content-release:frontend-version:get vets_website | tail -1)

# Create a fresh log file.
[ -f "${logfile}" ] && rm ${logfile}
touch ${logfile}

date >> ${logfile}

echo "next-build version: ${next_build_version}" >> ${logfile}
echo "vets-website version: ${vets_website_version}" >> ${logfile}

# Tell the frontend (and the user) that we're starting.
#drush va-gov:content-release:advance-state starting
echo "==> Starting a frontend build. This file will be updated as the build progresses." >> ${logfile}

# Reset the repos to defaults.
#echo "==> Resetting VA repos to default versions" >> ${logfile}
#rm -rf ${reporoot}/docroot/vendor/va-gov
#composer install --no-scripts &>> ${logfile}

# Get the requested next-build version
if [ "${next_build_version}" != "__default" ]; then
  echo "==> Checking out the requested frontend version" >> ${logfile}
  pushd ${reporoot}/next
  if echo "${next_build_version}" | grep -qE '^[0-9]+$' > /dev/null; then
    echo "==> Checking out PR #${next_build_version}"
    git fetch origin pull/${next_build_version}/head &>> ${logfile}
  else
    echo "==> Checking out git ref ${next_build_version}"
    git fetch origin ${next_build_version} &>> ${logfile}
  fi
  git checkout FETCH_HEAD &>> ${logfile}
  popd
else
  echo "==> Using default next-build version" >> ${logfile}
fi

# Install 3rd party deps.
echo "==> Installing yarn dependencies" >> ${logfile}
composer va:next:install &>> ${logfile}

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
#drush va-gov:content-release:advance-state inprogress
composer va:next:build &>> ${logfile}

# Advance the state in the frontend so another build can start.
echo "==> Build complete" >> ${logfile}
#drush va-gov:content-release:advance-state complete
#drush va-gov:content-release:advance-state ready

# After this point, we are less concerned with errors; the build has completed.
set +e

# Switch to the docroot to run drush commands.
cd "${reporoot}/docroot"

# Log the timestamp of the build for reporting purposes.
drush state:set next_build.status.last_build_date "$(date)"

# Just in case it wasn't clear :)
echo "==> Done" >> ${logfile}
