@tooltip
Feature: Tooltips
  As a CMS editor,
  I want to be able to view tooltips
  So I have the information necessary to add and edit content

  Scenario: Display the last-updated tooltip
    Given I am logged in as a user with the "content_publisher" role
    And I hover over "button.toolbar-icon-content-release"
    Then I should see "VA.gov last updated"
