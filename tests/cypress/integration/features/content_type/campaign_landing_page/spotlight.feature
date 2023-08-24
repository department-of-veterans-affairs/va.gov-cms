@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable Spotlight page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "Spotlight"
    And I enable the page segment
    Then I fill in field with selector "#edit-field-clp-spotlight-header-0-value" with fake text
    And I fill in field with selector "#edit-field-clp-spotlight-intro-text-0-value" with fake text
    Then I click the "Add Call to action" button
    And I fill in "Link" field with fake link
    And I fill in "Link text" field with fake text
    Then I click the "Add Link teaser" button
    And I fill in "URL" field with fake link
    And I fill in "Link summary" field with fake text
