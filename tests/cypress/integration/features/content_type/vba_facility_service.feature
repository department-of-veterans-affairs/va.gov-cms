@content_type__vba_facility_service
Feature: CMS User may effectively interact with the VBA Facility service form
  In order to confirm that cms user have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  @critical_path
  Scenario: Log in and try to create a VBA Facility service as a VBA editor
    When I am logged in as a user with the roles "content_creator_vba, content_publisher"
    And my workbench access sections are set to "1065"
    And I am at "/node/add/vba_facility_service"
    Then I should see an option with the text "Columbia VA Regional Benefit Office" from dropdown with selector "#edit-field-office"
    And I should not see an option with the text "Cheyenne VA Regional Benefit Office" from dropdown with selector "#edit-field-office"

  @critical_path
  Scenario: Log in and try to create a VBA Facility service as a VBA editor with 2 sections
    When I am logged in as a user with the roles "content_creator_vba, content_publisher"
    And my workbench access sections are set to "1065,1104"
    And I am at "/node/add/vba_facility_service"
    Then I should see an option with the text "Cheyenne VA Regional Benefit Office" from dropdown with selector "#edit-field-office"
    And I should see an option with the text "Columbia VA Regional Benefit Office" from dropdown with selector "#edit-field-office"
    And I should not see an option with the text "Denver VA Regional Benefit Office" from dropdown with selector "#edit-field-office"

