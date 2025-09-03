Feature: API

  Scenario: JSON API consumer can access route translation for a given locations_listing
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Flocations"
  Scenario: JSON API consumer can access locations_listing nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/locations_listing?page[limit]=1&filter[status]=1&include=field_office%2Cfield_administration"
