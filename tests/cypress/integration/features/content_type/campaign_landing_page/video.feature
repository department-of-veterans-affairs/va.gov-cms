@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable Video page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "Video"
    And I enable the page segment
    Then I can fill in field with selector "#edit-field-clp-video-panel-header-0-value" with fake text
    And I should see an element with the selector "#edit-field-media-open-button"
# Add media modal is tested in basic_requirements.feature
    And I should see "Add a link to more videos"
    When I click the "Add Call to action" button
    Then I can fill in "Link" field with fake link
    And I can fill in "Link text" field with fake text
