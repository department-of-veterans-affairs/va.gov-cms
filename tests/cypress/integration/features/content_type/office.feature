@content_type__office
Feature: Content Type: Office

  @critical_path
  Scenario: Log in and create an office.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "office" node
