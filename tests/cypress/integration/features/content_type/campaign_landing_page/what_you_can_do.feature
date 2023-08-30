@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Test that expected form elements are present in What You Can Do segment
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "What you can do"
    Then I can fill in field with selector "#edit-field-clp-what-you-can-do-header-0-value" with fake text
    And I can fill in field with selector "#edit-field-clp-what-you-can-do-intro-0-value" with fake text
    And I should see "What you can do promos"
    When I click the "Add new Promo" button
# TODO: Test if Add media button is there
    And I can fill in "URL" field with fake link
    And I can fill in "Link summary" field with fake text
    When I click the "Cancel" button
# TODO: Test Add existing promo feature and modal
