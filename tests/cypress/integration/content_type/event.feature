@content_type__event
Feature: Content Type: Event

  Scenario: Log in and create an event.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "event" node
