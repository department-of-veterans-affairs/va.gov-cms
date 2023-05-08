@taxonomy__va_benefits_taxonomy
Feature: Taxonomy: VA Benefits

  Scenario: Log in and create a va benefit.
    Given I am logged in as a user with the "administrator" role
    Then I create a "va_benefits_taxonomy" taxonomy term
