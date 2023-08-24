@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Enable Events page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "Events"
    And I enable the page segment
    And I fill in field with selector "#edit-field-clp-events-header-0-value" with fake text
#    Then I should see an element with the selector "#edit-field-clp-events-references-entity-browser-entity-browser-open-modal"
    And I click the "Select events" button
    And I wait "3" seconds
    And I make a selection
    Then I click the "Select events" button
