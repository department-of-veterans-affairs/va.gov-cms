Feature: API

  Scenario: JSON API consumer can access route translation for a given story_listing
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fstories"
  Scenario: JSON API consumer can access story_listing nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/story_listing?page[limit]=1&include=field_office"
  Scenario: JSON API consumer can follow the event listing flow for a given path
    Given I am an anonymous user
    And I can successfully follow the "story_listing" API flow for "/boston-health-care/stories"