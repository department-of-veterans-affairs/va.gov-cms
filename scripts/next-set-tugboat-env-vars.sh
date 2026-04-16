#!/usr/bin/env bash

# Put necessary env variables in place for next's Drupal Preview before building server
# These override the default values shipped in the repo.
# The default values point to the standard lower CMS environment.
echo "Setting up Tugboat environment variables for Next.js..."
sed -i "s|https://mirror.cms.va.gov|${TUGBOAT_DEFAULT_SERVICE_URL}|g" "${TUGBOAT_ROOT}/next/envs/.env.tugboat"
echo "DRUPAL_CLIENT_ID=${DRUPAL_CLIENT_ID}" >> ${TUGBOAT_ROOT}/next/envs/.env.tugboat
echo "DRUPAL_CLIENT_SECRET=${DRUPAL_CLIENT_SECRET}" >> ${TUGBOAT_ROOT}/next/envs/.env.tugboat
