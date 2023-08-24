@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Test expected form elements in Hero Banner segment
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I fill in "Page title" field with fake text
    And I fill in "Page introduction" field with fake text
    And I should see "Primary call to action"
    And I fill in autocomplete field with selector "#edit-field-primary-call-to-action-0-subform-field-button-link-0-uri" with value "va.gov"
    And I fill in autocomplete field with selector "#edit-field-primary-call-to-action-0-subform-field-button-label-0-value" with value "VA Website"
    And I should see "Hero Image"
    # basic_requirements.feature tests the add media button
    And I should see the "Add media" button
    Then I click to collapse "Hero banner"
