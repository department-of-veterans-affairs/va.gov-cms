Feature: API

  Scenario: JSON API consumer can access person_profile nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "jsonapi/node/person_profile?page[limit]=1&filter[status]=1&include=field_media%2Cfield_media.image%2Cfield_office%2Cfield_complete_biography%2Cfield_telephone%2Cfield_administration"
