@content_editing_vamc_banner_reuse
Feature: VAMC full_width_banner_alert editors should not be able to reuse banners.

Scenario: Log in and create VAMC Full Width Banner Alert
  When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
  And my workbench access sections are set to "205"
  And I am at "/node/add/full_width_banner_alert"
  And I select option "---VA Boston health care" from dropdown "Section"
#  And I check the "VA Boston health care" checkbox
  And I select option "Information" from dropdown "Alert type"
  And I fill in "Title" with "[Test Data] Banner alert title"
  And I fill in ckeditor "field-body-0-value" with "[Test Data] Banner alert Body"
  And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
  And I save the node
  And I wait "10" seconds
  Then the "Edit" tab should exist
  Given I click the edit tab
  And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message publish."
  And I check the Published checkbox
  And I save the node
  And I wait "10" seconds
  Then the "Edit" tab should exist
  Given I click the edit tab
  And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message unpublish."
  And I uncheck the Published checkbox
  And I save the node
  And I wait "10" seconds
  Then the "Edit" tab should not exist
