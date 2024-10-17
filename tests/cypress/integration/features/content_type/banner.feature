@content_type__banner
Feature: Content Type: Banner

  @critical_path
  Scenario: Log in and create a banner.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "banner" node
