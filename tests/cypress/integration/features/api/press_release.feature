Feature: API

  Scenario: JSON API consumer can access press_release nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "jsonapi/node/press_release?page[limit]=1&filter[status]=1&include=field_press_release_downloads%2Cfield_press_release_contact%2Cfield_press_release_contact.field_telephone%2Cfield_listing%2Cfield_administration"
