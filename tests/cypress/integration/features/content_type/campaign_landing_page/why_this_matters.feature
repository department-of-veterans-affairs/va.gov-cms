@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Test that expected form elements are present in Why This Matters segment
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to expand "Why this matters"
    Then I fill in field with selector "#edit-field-clp-why-this-matters-0-value" with fake text
    And I click to expand "Select up to 3 audiences"
    Then I click the "Select audiences" button
    And I wait "3" seconds
    And I make a selection
    And I click the "Select audiences" button
    And I wait "3" seconds
    And I should see "Secondary call to action"
    And I click the "Add Call to action" button
    And I fill in "Link" field with fake link
    And I should see "Link text"
#    And I fill in "Link text" field with fake text
