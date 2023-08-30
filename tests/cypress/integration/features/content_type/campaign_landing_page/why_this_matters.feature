@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Test that expected form elements are present in Why This Matters segment
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/campaign_landing_page"
    And I click to expand "Why this matters"
    Then I can fill in field with selector "#edit-field-clp-why-this-matters-0-value" with fake text
    When I click to expand "Select up to 3 audiences"
    Then I should see "Select audiences"
    # TODO: Test audience selection modal
    And I should see "Secondary call to action"
    When I click the "Add Call to action" button
    Then I can fill in "Link" field with fake link
    And I should see "Link text"
