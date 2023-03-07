#!/usr/bin/env bash

set -ex

export failure=0

declare -a files=(
  "config/sync/cer.corresponding_reference.banner_alert_and_vamc_operating_status.yml"
  "config/sync/cer.corresponding_reference.hub_promo_block.yml"
  "config/sync/cer.corresponding_reference.local_health_service_to_facility.yml"
  "config/sync/cer.corresponding_reference.local_health_service_to_regions_health_service.yml"
  "config/sync/cer.corresponding_reference.region_to_regional_health_service_offering.yml"
)

for file in "${files[@]}"; do
  if [[ ! -f "$file" ]]; then
    failure=1
    echo "The cer field file named ${file} does not exist."
  fi
done

if [ "${failure}" -eq 1 ]; then
  echo "To fix this test, ensure that there are 5 cer fields, here: /admin/config/content/cer
  1) Banner alert and VAMC operating status
  2) Hub Promo Block
  3) Local Health Service Offering to Facility
  4) Local Health Service Offering to Regional Health Service Offering
  5) Region to Regional Health Service Offering"
  exit 1
fi

exit 0
