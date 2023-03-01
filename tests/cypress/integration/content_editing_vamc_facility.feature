@content_editing_vamc_facility
Feature: CMS Users may effectively interact with the VAMC Facility form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and create a VAMC Facility as an admin
    Given I am logged in as a user with the "content_admin" role
    When I am at "/node/add/health_care_local_facility"
    And I fill in "Name of facility" with "[Test Data] Facility Name"
    And I select the radio button with the value "1037"
    Then I should see "Visitors are welcome" in ckeditor "field-supplemental-status-more-i-0"
    And I select the radio button with the value "1036"
    Then I should see "Your care partner is welcome" in ckeditor "field-supplemental-status-more-i-0"
    And I select the radio button with the value "1035"
    Then I should see "Approved visitors only" in ckeditor "field-supplemental-status-more-i-0"
    Then I fill in ckeditor "field-supplemental-status-more-i-0" with "[Test Data] COVID 19 Status Details"
    Then I select option "VA Alaska health care" from dropdown "What health care system does the facility belong to?"
    And I fill in "Meta description" with "[Test Data] Meta description"
    Then I select option "---VA Alaska health care" from dropdown "Section"
    And I fill in "Menu link title" with "[Test Data] Menu link title"
    Then I select option "-- VA Alaska health care" from dropdown "Parent link"
    Then I scroll to position "bottom"
    And I click the "Save draft and continue editing" button
    Then I should see "[Test Data] COVID 19 Status Details" in ckeditor "field-supplemental-status-more-i-0"
