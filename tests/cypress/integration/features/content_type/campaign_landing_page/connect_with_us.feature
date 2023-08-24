@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Check that Connect With Us page segment has expected fields/selections
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to expand "Connect with us"
    Then I select option "NCA" from dropdown with selector "#edit-field-related-office"
    And I select option "VACO" from dropdown with selector "#edit-field-related-office"
    And I select option "VBA" from dropdown with selector "#edit-field-related-office"
    And I select option "VHA" from dropdown with selector "#edit-field-related-office"
