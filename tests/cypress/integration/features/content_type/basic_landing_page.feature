@content_type__basic_landing_page
Feature: Content Type: Basic Landing Page

  @critical_path
  Scenario: Log in and create a basic_landing_page.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "basic_landing_page" node
