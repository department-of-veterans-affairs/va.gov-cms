Feature: API

  Scenario: JSON API consumer can access route translation for a given leadership_listing
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fabout-us%2Fleadership"
  Scenario: JSON API consumer can access leadership_listing nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/leadership_listing?page[limit]=1&include=field_leadership%2Cfield_office%2Cfield_leadership.field_media.image%2Cfield_leadership.field_telephone%2Cfield_leadership.field_office%2Cfield_administration"
