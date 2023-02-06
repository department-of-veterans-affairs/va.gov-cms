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
    And I wait "2" seconds
    Then I select the "Medium" radio button
    And I wait "2" seconds
    Then I should see "medium" in ckeditor "cke_edit-field-supplemental-status-more-i-0-value"
    And I wait "2" seconds
    Then I select the "High" radio button
    And I wait "2" seconds
    Then I should see "high" in ckeditor "cke_edit-field-supplemental-status-more-i-0-value"
    And I wait "2" seconds
    Then I select the "Low" radio button
    And I wait "2" seconds
    Then I should see "low" in ckeditor "cke_edit-field-supplemental-status-more-i-0-value"
