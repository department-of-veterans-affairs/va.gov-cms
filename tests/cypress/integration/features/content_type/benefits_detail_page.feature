@content_type__benefits_detail_page
Feature: Content Type: Benefits Detail Page

  @critical_path
  Scenario: Log in and create a benefits detail page
    Given I am logged in as a user with the "content_admin" role
    Then I create a "page" node