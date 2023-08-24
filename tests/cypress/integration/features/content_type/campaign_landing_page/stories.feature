@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable Stories page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "Stories"
    And I enable the page segment
    Then I fill in field with selector "#edit-field-clp-stories-header-0-value" with fake text
    And I fill in field with selector "#edit-field-clp-stories-intro-0-value" with fake text
    And I click the "Add story" button
    And I click the "Add media" button
    And I make a selection
    And I click the "Insert selected" button
    And I wait "3" seconds
    Then I fill in "URL" field with fake link
#    And I fill in "Link text" field with fake text
    And I fill in "Link summary" field with fake text
    And I fill in "Add a link to an external blog or other list of stories" field with fake text
