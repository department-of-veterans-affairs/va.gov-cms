@content_type__benefits_hub_landing_page
Feature: Content Type: Benefits Hub Landing Page

  @critical_path
  Scenario: Log in and create a Benefits Hub Landing Page.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "landing_page" node

  Scenario: Confirm that content cannot be published directly from the node view but can from the node edit form.
    Given I am logged in as a user with the "content_admin" role
    And I create a "landing_page" node
    Then an element with the selector "#edit-new-state" should not exist
    And I edit the node
    Then the element with selector "#edit-moderation-state-0-state" should contain "Draft"
