@content_type__documentation_page
Feature: Content Type: Documentation Page

  @critical_path
  Scenario: Log in and create a documentation_page node.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "documentation_page" node
