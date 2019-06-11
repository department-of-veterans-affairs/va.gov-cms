
Feature: The site is generally working.
  
  Scenario: Log in and poke around
    Given I am on the homepage
    Then I am logged in as a user with the role "admin"
