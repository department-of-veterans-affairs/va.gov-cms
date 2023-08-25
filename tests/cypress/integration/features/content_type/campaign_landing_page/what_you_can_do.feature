@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  Scenario: Test that expected form elements are present in What You Can Do segment
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    Then I click to expand "What you can do"
    And I fill in field with selector "#edit-field-clp-what-you-can-do-header-0-value" with fake text
    And I fill in field with selector "#edit-field-clp-what-you-can-do-intro-0-value" with fake text
    And I should see "What you can do promos"
    And I click the "Add new Promo" button
    # TODO: Test Promo feature separately

  Scenario: Test that "Add existing Promo" button is available in "What You Can Do" segment
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to collapse "Hero banner"
    And I click to expand "What you can do"
    Then I click the "Add existing Promo" button
    # TODO: Test Promo feature separately
