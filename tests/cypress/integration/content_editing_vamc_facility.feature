@content_editing_vamc_facility
Feature: CMS Users may effectively interact with the VAMC Facility form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and edit a VAMC Facility as an editor
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "372"
    When I am at "/alaska-health-care/locations/anchorage-va-medical-center"
    And I click the edit tab
    And I select the radio button with the value "1037"
    And I wait "2" seconds
    Then I should see "low" in ckeditor "field-supplemental-status-more-i-0"
    And I select the radio button with the value "1036"
    And I wait "2" seconds
    Then I should see "medium" in ckeditor "field-supplemental-status-more-i-0"
    And I select the radio button with the value "1035"
    And I wait "2" seconds
    Then I should see "high" in ckeditor "field-supplemental-status-more-i-0"
    Then I fill in ckeditor "field-supplemental-status-more-i-0" with "[Test Data] COVID 19 Status Details"
    Then I scroll to position "bottom"
    And I click the "Save draft and continue editing" button
    Then I should see "[Test Data] COVID 19 Status Details" in ckeditor "field-supplemental-status-more-i-0"

  Scenario: Log in and create a VAMC Facility as an admin
    Given I am logged in as a user with the "content_admin" role
    When I am at "/node/add/health_care_local_facility"
    And I select the radio button with the value "1037"
    And I wait "2" seconds
    Then I should see "low" in ckeditor "field-supplemental-status-more-i-0"
    And I select the radio button with the value "1036"
    And I wait "2" seconds
    Then I should see "medium" in ckeditor "field-supplemental-status-more-i-0"
    And I select the radio button with the value "1035"
    And I wait "2" seconds
    Then I should see "high" in ckeditor "field-supplemental-status-more-i-0"
