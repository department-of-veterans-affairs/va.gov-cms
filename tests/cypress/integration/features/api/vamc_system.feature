Feature: API

  Scenario: JSON API consumer can access route translation for a given vamc_system
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care"
  Scenario: JSON API consumer can access vamc_system nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care"
