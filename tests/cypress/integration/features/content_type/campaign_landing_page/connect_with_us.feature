@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Check that Connect With Us page segment has expected fields/selections
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/campaign_landing_page"
    And I click to expand "Connect with us"
    Then I should see an option with the text "NCA" from dropdown with selector "#edit-field-related-office"
    And I should see an option with the text "VACO" from dropdown with selector "#edit-field-related-office"
    And I should see an option with the text "VBA" from dropdown with selector "#edit-field-related-office"
    And I should see an option with the text "VHA" from dropdown with selector "#edit-field-related-office"
