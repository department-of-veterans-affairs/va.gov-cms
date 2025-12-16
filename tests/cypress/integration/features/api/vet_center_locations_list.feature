Feature: API

  Scenario: JSON API consumer can access route translation for a given vet_center_locations_list
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fcolorado-springs-vet-center%2Flocations"
  Scenario: JSON API consumer can access vet_center_locations_list nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/vet_center_locations_list?page[limit]=1&filter[status]=1&include=field_office%2Cfield_office.field_media%2Cfield_office.field_media.image%2Cfield_nearby_mobile_vet_centers%2Cfield_nearby_mobile_vet_centers.field_media%2Cfield_nearby_mobile_vet_centers.field_media.image"
  Scenario: JSON API consumer can access vet_center_cap nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/vet_center_cap?page[limit]=1&filter[status]=1&include=field_media.image"
  Scenario: JSON API consumer can access vet_center_outstation nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/vet_center_outstation?page[limit]=1&filter[status]=1&include=field_media.image"
  Scenario: JSON API consumer can access vet_center_mobile_vet_center nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/vet_center_mobile_vet_center?page[limit]=1&filter[status]=1&include=field_media.image"