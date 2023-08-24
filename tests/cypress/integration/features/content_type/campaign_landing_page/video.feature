@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable Video page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "Video"
    And I enable the page segment
    Then I fill in field with selector "#edit-field-clp-video-panel-header-0-value" with fake text
    And I click the "Add media" button
    And I make a selection
    And I click the "Insert selected" button
    And I wait "5" seconds
    Then I should see "Add a link to more videos"
#    And I click the "Add Call to action" button
#    And I fill in "Link" field with fake link
#    And I fill in "Link text" field with fake text
