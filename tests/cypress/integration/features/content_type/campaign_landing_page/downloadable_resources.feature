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
# TODO: Test section drop-down for this segment
    And I click the "Create Downloadable resource" button
    And I click the "Cancel" button
    And I click the "Add existing Downloadable resource" button
    And I should see "Select Downloadable resource"
# TODO: Test clicking the "Select Downloadable resource" button and modal
    And I should see "Downloadable resources cta"
    And I click the "Add Call to action" button
    And I fill in "Link" field with fake link
# TODO: Test the Link text field
