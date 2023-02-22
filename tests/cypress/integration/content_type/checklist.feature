@content_type__checklist
Feature: Content Type: Checklist

  Scenario: Log in and create a checklist.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "checklist" node
