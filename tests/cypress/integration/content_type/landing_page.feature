@content_type__benefits_hub_landing_page
Feature: Content Type: Benefits Hub Landing Page

  Scenario: Log in and create a Benefits Hub Landing Page.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "landing_page" node
