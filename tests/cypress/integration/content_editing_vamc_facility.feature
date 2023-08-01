@content_editing_vamc_facility
Feature: CMS Users may effectively interact with the VAMC Facility form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and create a VAMC Facility as an admin
    Given I am logged in as a user with the "content_admin" role
    When I am at "/node/add/health_care_local_facility"
    And I fill in "Name of facility" with "[Test Data] Facility Name"
    Then I select option "VA Alaska health care" from dropdown "What health care system does the facility belong to?"
    And I fill in "Meta description" with "[Test Data] Meta description"
    Then I select option "---VA Alaska health care" from dropdown "Section"
    And I fill in "Menu link title" with "[Test Data] Menu link title"
    Then I select option "-- VA Alaska health care" from dropdown "Parent link"
    Then I scroll to position "bottom"
    And I fill in "Revision log message" with "[Test data] Revision message"
    And I click the "Save draft and continue editing" button
