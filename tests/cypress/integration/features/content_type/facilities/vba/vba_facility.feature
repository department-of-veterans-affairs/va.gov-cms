@content_type__vba_facility
Feature: CMS User may effectively interact with the VBA Facility form
  In order to confirm that cms user have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and try to edit an archived VBA Facility as a VBA editor
    When I am logged in as a user with the roles "content_creator_vba, content_publisher"
    # Columbia VA Regional Benefit Office
    And my workbench access sections are set to "1065"
    # Fort Jackson Satelite office - an archived facility in that section.
    Then I am at "/node/4071/"
    Then the primary tab "View" should exist
    Then the primary tab "Edit" should not exist

  Scenario: Enable banner segment and ensure expected fields are present
    Given I am logged in as a user with the "content_admin" role
    And my workbench access sections are set to "1065"
    # Columbia VA Regional Benefit Office
    When I am at "/node/4063/edit"
    # Banner related fields should not be visible.
    And I scroll to element '#edit-field-show-banner-value'
    And I uncheck the "Display a banner alert on this facility" checkbox
    Then I should not see an element with the selector "#edit-field-alert-type"
    And I should not see an element with the selector "#edit-field-dismissible-option--wrapper"
    And I should not see an element with the selector "#edit-field-banner-title-0-value"
    And I should not see an element with the selector "#edit-field-banner-content-wrapper"
    When I check the "Display a banner alert on this facility" checkbox
    # Banner related fields should be visible.
    Then I should see an element with the selector "#edit-field-alert-type"
    And I should see an element with the selector "#edit-field-dismissible-option--wrapper"
    And I should see an element with the selector "#edit-field-banner-title-0-value"
    And I should see an element with the selector "#edit-field-banner-content-wrapper"
    # Banner field data should not persist if it is disabled.
    When I select option "Information" from dropdown "Banner alert type"
    And I select the "Allow site visitors to dismiss banner" radio button
    And I fill in field with selector "#edit-field-banner-title-0-value" with value "[Test Data] Test banner title."
    And I fill in ckeditor "edit-field-banner-content-0-value" with "[Test Data] Banner Body"
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I uncheck the "Display a banner alert on this facility" checkbox
    And I save the node
    Then I should see "VBA Facility Columbia VA Regional Benefit Office has been updated."
    When I am at "/node/4063/edit"
    And I scroll to element '#edit-field-show-banner-value'
    And I check the "Display a banner alert on this facility" checkbox
    Then an element with the selector "#edit-field-banner-title-0-value" should be empty
    And the option "- Select a value -" from dropdown with selector "#edit-field-alert-type" should be selected
