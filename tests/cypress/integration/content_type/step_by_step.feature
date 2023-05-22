@content_type__step_by_step
Feature: Content Type: Step-by-Step

  Scenario: Log in and create a checklist.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "step_by_step" node
