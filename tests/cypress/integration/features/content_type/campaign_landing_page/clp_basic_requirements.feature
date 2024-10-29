@content_type__campaign_landing_page
Feature: Content Type: Campaign Landing Page

  @critical_path
  Scenario: Log in and create a campaign_landing_page.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "campaign_landing_page" node
