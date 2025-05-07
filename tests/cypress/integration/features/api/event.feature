Feature: API

  Scenario: JSON API consumer can access event nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "jsonapi/node/event?page[limit]=1&include=field_media%2Cfield_media.image%2Cfield_listing%2Cfield_listing.field_office%2Cfield_administration%2Cfield_facility_location"
