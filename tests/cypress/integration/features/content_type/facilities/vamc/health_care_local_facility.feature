@content_editing_vamc_facility
Feature: CMS Users may effectively interact with the VAMC Facility form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  @critical_path
  Scenario: Log in and create a VAMC Facility as an admin
    Given I am logged in as a user with the "content_admin" role
    When I am at "/node/add/health_care_local_facility"
    And I fill in "Name of facility" with "[Test Data] Facility Name"
    And I select the "Normal services and hours" radio button
    And I select option "VA Alaska health care" from dropdown "What health care system does the facility belong to?"
    And I fill in "Meta description" with "[Test Data] Meta description"
    And I select option "---VA Alaska health care" from dropdown "Section"
    And I fill in "Menu link title" with "[Test Data] Menu link title"
    And I select option "-- VA Alaska health care" from dropdown "Parent link"
    Then an element with the selector '[data-drupal-selector="edit-group-covid-19-safety-guidelines"]' should not exist
    And I scroll to position "bottom"
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I click the "Save draft and continue editing" button
    Then "[Test Data] Facility Name" should exist
