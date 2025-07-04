Feature: API

  Scenario: JSON API consumer can access route translation for a given event_listing
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fevents"
  Scenario: JSON API consumer can access event_listing nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/event_listing?page[limit]=1&include=field_administration&sort=-created"
  Scenario: JSON API consumer can follow the event listing flow for a given path
    Given I am an anonymous user
    And I can successfully follow the "event_listing" API flow for "/boston-health-care/events"