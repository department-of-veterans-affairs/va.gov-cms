Feature: API

  Scenario: JSON API consumer can access route translation for a given vamc_police
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fva-police"
  Scenario: JSON API consumer can access vamc_police nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fva-police"
