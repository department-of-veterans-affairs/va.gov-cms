Feature: API

  Scenario: JSON API consumer can access route translation for a given health_care_local_facility
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Flovell-federal-health-care-va%2Flocations%2Fcaptain-james-a-lovell-federal-health-care-center"
  Scenario: JSON API consumer can access health_care_local_facility nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/health_care_local_facility?filter%5Bstatus%5D%5Bvalue%5D=1&include=field_region_page.field_related_links.field_va_paragraphs%2Cfield_media%2Cfield_media.image%2Cfield_administration%2Cfield_telephone%2Cfield_location_services%2Cfield_local_health_care_service_.field_regional_health_service.field_service_name_and_descripti%2Cfield_local_health_care_service_.field_administration%2Cfield_local_health_care_service_.field_service_location.field_phone%2Cfield_local_health_care_service_.field_service_location.field_other_phone_numbers%2Cfield_local_health_care_service_.field_service_location.field_service_location_address%2Cfield_local_health_care_service_.field_service_location.field_email_contacts&page%5Blimit%5D=1"