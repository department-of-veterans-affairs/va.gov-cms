@api
Feature: Content Migration: VA Benefits Burials
#  In order encourage web developers to start testing.
#  As a devshop developer
#  I need Behat tests to be setup and able to run out of the box.

  @migrate
  Scenario: Benefits/Burials Migration is ready
    Given I am logged in as a user with the "administrator" role
    When I am at "admin/structure/migrate/manage/va_tests/migrations/va_benefits_burials/execute"
    Then I should see "Execute migration"
    # Error is too generic.  The string error appears in several admin menu items.
    #Then I should not see "ERROR"
