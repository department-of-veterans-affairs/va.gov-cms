@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  @ignore
  Scenario: Log in and create a campaign_landing_page.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "campaign_landing_page" node

  Scenario: Test that expected form elements are present in Hero Banner segment
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I should see "Page title"
    And I should see "Page introduction"
    And I should see "Primary call to action"
    And I should see "Link"
    And I should see "Link text"
    And I should see "Hero Image"
    And I should see button "Add media"
    Then I click to collapse "Hero banner"

  Scenario: Test that expected form elements are present in Why This Matters segment
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    And I click to expand "Why this matters"
    Then I should see "Introduction"
    And I click to expand "Select up to 3 audiences"
    Then I should see "Select audiences"
    And I should see "Secondary call to action"
    And I should see "Add Call to action"
    Then I click to collapse "Why this matters"

  Scenario: Test that expected form elements are present in What You Can Do segment
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
    Then I click to expand "What you can do"
    And I should see "Heading"
    And I should see an element with the selector "#edit-field-clp-what-you-can-do-intro-0-value"
    And I should see "What you can do promos"
    And I should see "Add new Promo"
    And I should see "Add existing Promo"

  @ignore
  Scenario: Enable Video page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"

  @ignore
  Scenario: Enable Spotlight page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"

  @ignore
  Scenario: Enable Stories page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"

  @ignore
  Scenario: Enable Downloadable Resources page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"

  @ignore
  Scenario: Enable Events page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"

  @ignore
  Scenario: Enable FAQ page segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"

  @ignore
  Scenario: Check that Connect With Us page segment has expected fields/selections
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/campaign_landing_page"
