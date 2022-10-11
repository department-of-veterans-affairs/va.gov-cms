#!/usr/bin/env bash

content_types=(
  health_care_region_detail_page
  campaign_landing_page
  page
  vet_center
  office
  news_story
  press_release
  full_width_banner_alert
  health_care_local_facility
  event
  q_a
  press_releases_listing
  vet_center_facility_health_servi
  vamc_system_policies_page
  vamc_operating_status_and_alerts
  regional_health_care_service_des
  person_profile
)

time for content_type in "${content_types[@]}"; do
  echo;
  echo;
  echo "Processing content type ${content_type}.";
  time drush scr scripts/content/VACMS-9936-linky.php "${content_type}"; 
  echo;
  echo;
done;
