#!/usr/bin/env bash

# Exit if a command fails with a non-zero status code.
set -ex

if [ -n "${IS_DDEV_PROJECT}" ]; then
    APP_ENV="local"
elif [ -n "${TUGBOAT_ROOT}" ]; then
    APP_ENV="tugboat"
else
    APP_ENV="tugboat"
fi

# Find repo root -> $ROOT
ROOT=${TUGBOAT_ROOT:-${DDEV_APPROOT:-unknown}}
if [ "$ROOT" == "unknown" ]; then
  echo "[!] Could not determine the environment type. Aborting!"
  exit 1
fi

# For convenience.
cd $ROOT

# Store path to site default files directory.
filesdir="${ROOT}/docroot/sites/default/files"

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
vets_website_version=$(drush va-gov-content-release:frontend-version:get next_build_vets_website | tail -1)

# Create a fresh log file.
[ -f "${logfile}" ] && rm ${logfile}
touch ${logfile}

date >> ${logfile}

echo "next-build version: ${next_build_version}" >> ${logfile}
echo "vets-website version: ${vets_website_version}" >> ${logfile}

# Find any existing next-server process and kill it.
if pgrep -f "next-server" > /dev/null; then
  echo "==> Stopping existing next-server process" >> ${logfile}
  pkill -f "next-server" &>> ${logfile}
else
  echo "==> No existing next-server process found. Continuing." >> ${logfile}
fi

# Tell the frontend (and the user) that we're starting.
echo "==> Starting a frontend build. This file will be updated as the build progresses." >> ${logfile}

# Get the requested next-build version.
pushd ${ROOT}/next
# Reset the working directory to the last commit, to clean up any changes.
git reset --hard &>> ${logfile}
if [ "${next_build_version}" != "__default" ]; then
  echo "==> Checking out the requested frontend version" >> ${logfile}
  if echo "${next_build_version}" | grep -qE '^[0-9]+$' > /dev/null; then
    echo "==> Checking out PR #${next_build_version}"
    git fetch origin pull/${next_build_version}/head &>> ${logfile}
  else
    echo "==> Checking out git ref ${next_build_version}"
    git fetch origin ${next_build_version} &>> ${logfile}
  fi
  git checkout FETCH_HEAD &>> ${logfile}

  if [ "${APP_ENV}" == "tugboat" ]; then
      echo "Setting up Tugboat environment variables for Next.js..."
      ${ROOT}/scripts/next-set-tugboat-env-vars.sh
  fi
else
  echo "==> Using default next-build version" &>> ${logfile}
  git checkout main &>> ${logfile}
fi
popd

# Install 3rd party deps for next-build.
echo "==> Installing yarn dependencies" >> ${logfile}
composer va:next:install &>> ${logfile}

# Get the requested vets-website version
pushd ${ROOT}/vets-website
# Reset the working directory to the last commit.
git reset --hard &>> ${logfile}
if [ "${vets_website_version}" != "__default" ]; then
  echo "==> Checking out the requested vets-website version" >> ${logfile}
  if echo "$vets_website_version" | grep -qE '^[0-9]+$' > /dev/null; then
    echo "==> Checking out PR #${vets_website_version}"
    git fetch origin pull/${vets_website_version}/head &>> ${logfile}
  else
    echo "==> Checking out git ref ${vets_website_version}"
    git fetch origin ${vets_website_version} &>> ${logfile}
  fi
  git checkout FETCH_HEAD &>> ${logfile}
else
  echo "==> Using default vets-website version" &>> ${logfile}
  git checkout main &>> ${logfile}
fi
popd

# Create symlink between vets-website assets and next-build.
mkdir -p "${ROOT}/next/public"
ln -snf "${ROOT}/vets-website/build/localhost/generated" "${ROOT}/next/public/generated"

# Build vets-website again.
echo "==> Re-building Vets Website" >> ${logfile}
${ROOT}/scripts/vets-web-setup.sh &>> ${logfile}

# Run the build.
echo "==> Starting build" >> ${logfile}
composer va:next:build &>> ${logfile}

# Start next server.
echo "==> Starting next server" >> ${logfile}
composer va:next:start & &>> ${logfile}

# After this point, we are less concerned with errors; the build has completed.
set +e

# Switch to the docroot to run drush commands.
cd "${ROOT}/docroot"

# Log the timestamp of the build for reporting purposes.
drush state:set next_build.status.last_build_date "$(date)"

# Just in case it wasn't clear :)
echo "==> Done" >> ${logfile}
