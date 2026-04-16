Feature: API

  Scenario: JSON API consumer can access route translation for a given vamc_police
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fva-police"
  Scenario: JSON API consumer can access vamc_police nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/vamc_system_va_police?page[limit]=1&filter[status]=1&include=field_administration%2Cfield_office%2Cfield_phone_numbers_paragraph"
