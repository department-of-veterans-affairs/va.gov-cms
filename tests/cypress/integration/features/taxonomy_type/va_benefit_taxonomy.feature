@taxonomy__va_benefits_taxonomy
Feature: Taxonomy: VA Benefits

  Scenario: Confirm that content admins access and/or edit certain form elements
    Given I am logged in as a user with the "content_admin" role
    When I am at "admin/structure/taxonomy/manage/va_benefits_taxonomy/add"
    Then I should see "Official Benefit name"
    And I should see "The full name of the benefit."
    And an element with the selector "#edit-name-0-value" should not be disabled
    And an element with the selector "#edit-field-va-benefit-api-id-0-value" should exist
    And an element with the selector "#edit-relations" should not exist

  Scenario: Log in and create a va benefit.
    Given I am logged in as a user with the "administrator" role
    Then I create a "va_benefits_taxonomy" taxonomy term
