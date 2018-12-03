@api @headless
Feature: Headless Lightning

  Scenario: Headless Lightning profile is installed
    Given I am logged in as a user with the administrator role
    When I visit "/admin/reports/status"
    Then I should see "Headless Lightning"
