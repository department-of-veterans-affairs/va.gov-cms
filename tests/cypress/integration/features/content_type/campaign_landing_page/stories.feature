@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable Stories page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "Stories"
    And I enable the page segment
    Then I can fill in field with selector "#edit-field-clp-stories-header-0-value" with fake text
    And I can fill in field with selector "#edit-field-clp-stories-intro-0-value" with fake text
    When I click the "Add story" button
    Then I should see "Link teaser with image"
# Add media modal is tested in basic_requirements.feature
    And I can fill in "URL" field with fake link
    And I can fill in "Link summary" field with fake text
    And I can fill in "Add a link to an external blog or other list of stories" field with fake text
