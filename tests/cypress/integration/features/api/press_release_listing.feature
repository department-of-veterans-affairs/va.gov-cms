Feature: API

  Scenario: JSON API consumer can access route translation for a given press release listing
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fnews-releases"
  Scenario: JSON API consumer can access press release nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/press_releases_listing?page[limit]=1&include=field_administration&sort=-created"
  Scenario: JSON API consumer can follow the event listing flow for a given path
    Given I am an anonymous user
    And I can successfully follow the "press_releases_listing" API flow for "/boston-health-care/news-releases"