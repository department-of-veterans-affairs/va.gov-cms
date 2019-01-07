@api
Feature: Collapsible Panel Migration
  As a developer
  I want to make sure that content was properly migrated

  @migration @collapsiblepanel
  Scenario: Ensure that multiple collapsible panels on the same page are imported
    Given I am logged in as a user with the administrator role
    When I visit "/va-mental-health-services"
    Then I should see 2 ".paragraph--type--collapsible-panel" elements

  @migration @collapsiblepanel
  Scenario: Ensure that all the items in both panel groups are imported
    Given I am logged in as a user with the administrator role
    When I visit "/va-mental-health-services"
    Then I should see 11 ".paragraph--type--collapsible-panel-item" elements

  @migration @collapsiblepanel
  Scenario: Ensure that the link content came through correctly
    Given I am logged in as a user with the administrator role
    When I visit "/champva-benefits"
    Then I should see "A new or expectant parent" in the ".paragraph--type--collapsible-panel-item .field--name-field-title" element
    And I should see "If you’re expecting a baby, you’ll need to take the 2 steps listed below" in the ".paragraph--type--collapsible-panel-item .field--name-field-wysiwyg" element



