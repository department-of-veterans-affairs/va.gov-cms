@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable Downloadable Resources page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "Downloadable resources"
    And I enable the page segment
    Then I fill in field with selector "#edit-field-clp-resources-header-0-value" with fake text
    And I fill in field with selector "#edit-field-clp-resources-intro-text-0-value" with fake text
    And I click the "Add new Downloadable resource" button
    And I fill in "Name" field with fake text
    And I fill in "External File URL" field with fake text
    And I fill in "Description" field with fake text
    And I should see "Select a section"
#    And I select option "VACO" from dropdown with selector "#edit-field-clp-resources-form-0-field-owner"
    And I click the "Create Downloadable resource" button
    And I click the "Cancel" button
    And I click the "Add existing Downloadable resource" button
    And I click the "Select Downloadable resource" button
    And I wait "3" seconds
    And I make a selection
    And I click the button with selector "#edit-submit"
    And I wait "3" seconds
    And I click the "Add Call to action" button
    And I fill in "Link" field with fake link
    And I fill in "Link text" field with fake text
