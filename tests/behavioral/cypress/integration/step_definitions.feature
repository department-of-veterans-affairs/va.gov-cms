@step_definitions
Feature: Step definitions function as expected
  In order to ensure reliable behavioral tests
  As a strong beautiful engineer
  I need my step definitions validated

  Scenario: I select the radio button
    Given I am logged in as a user with the "administrator" role
    And I am at "/admin/content-models/users"
    And I select the "Visitors" radio button
    Then the "Visitors" radio button should be selected
    When I select the "Administrators only" radio button
    Then the "Administrators only" radio button should be selected
