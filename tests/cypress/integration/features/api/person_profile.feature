Feature: API

  Scenario: JSON API consumer can access event nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "jsonapi/node/person_profile?page[limit]=1&include=field_media%2Cfield_media.image&filter[status]=1"
