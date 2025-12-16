Feature: API

  Scenario: JSON API consumer can access route translation for a given health_services_listing
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fhealth-services"
  Scenario: JSON API consumer can access health_services_listing nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/health_services_listing?page[limit]=1&filter[status]=1&include=field_office%2Cfield_administration%2Cfield_featured_content_healthser"
  Scenario: JSON API consumer can access regional_health_care_service_des nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/regional_health_care_service_des?page[limit]=1&filter[status]=1&include=field_service_name_and_descripti%2Cfield_local_health_care_service_%2Cfield_local_health_care_service_.field_facility_location"