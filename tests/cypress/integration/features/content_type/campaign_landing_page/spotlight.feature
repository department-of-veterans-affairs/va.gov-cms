@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable Spotlight page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "Spotlight"
    And I enable the page segment
    Then I can fill in field with selector "#edit-field-clp-spotlight-header-0-value" with fake text
    And I can fill in field with selector "#edit-field-clp-spotlight-intro-text-0-value" with fake text
    And I should see "Optional Spotlight cta"
# TODO: Test call to action button feature
    When I click the "Add Link teaser" button
    Then I can fill in "URL" field with fake link
    And I can fill in "Link summary" field with fake text
