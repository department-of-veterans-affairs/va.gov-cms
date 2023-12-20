@content_type__q_a
Feature: Content Type: Reusable Q&A

  Scenario: Log in and create a Reusable Q and A node.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "q_a" node
