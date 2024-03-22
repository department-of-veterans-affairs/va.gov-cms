#!/usr/bin/env bash

# Put necessary env variables in place for next's Drupal Preview before building server
# Need to construct this way instead of TUGBOAT_DEFAULT_SERVICE_URL in order to drop the trailing /
echo "Setting up Tugboat environment variables for Next.js..."
echo "NEXT_PUBLIC_DRUPAL_BASE_URL=https://cms-${TUGBOAT_SERVICE_TOKEN}.${TUGBOAT_SERVICE_CONFIG_DOMAIN}" >> ${TUGBOAT_ROOT}/next/envs/.env.tugboat
echo "NEXT_IMAGE_DOMAIN=https://cms-${TUGBOAT_SERVICE_TOKEN}.${TUGBOAT_SERVICE_CONFIG_DOMAIN}" >> ${TUGBOAT_ROOT}/next/envs/.env.tugboat
echo "DRUPAL_CLIENT_ID=${DRUPAL_CLIENT_ID}" >> ${TUGBOAT_ROOT}/next/envs/.env.tugboat
echo "DRUPAL_CLIENT_SECRET=${DRUPAL_CLIENT_SECRET}" >> ${TUGBOAT_ROOT}/next/envs/.env.tugboat
