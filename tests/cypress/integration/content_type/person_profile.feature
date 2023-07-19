@content_editing_person_profile
Feature: Person Profile creation.

Scenario: Log in and create a Person Profile with attention to conditional fields.
  # Create the page with no intention of using biography.
  When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
  And my workbench access sections are set to "205"
  And I am at "/node/add/person_profile"
  And I select option "---VA Boston health care" from dropdown "Section"
  And I select option "VA Boston health care" from dropdown "Related office or health care region"
  And I fill in "First name" with "James"
  And I fill in "Last name" with "Smith"
  And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
  And I click the "Save" button
  Then I should see "Staff Profile James Smith has been created."

  # Create the page with intention of using biography without providing both required.
  Given I click the edit tab
  And I check the "Create profile page with biography" checkbox
  And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
  And I click the "Save" button
  Then I am prevented from saving the node by "textarea" "#edit-field-intro-text-0-value" with error "Please fill out this field."

  # Create the page with intention of using biography but only providing First sentence.
  Given I fill in field with selector "#edit-field-intro-text-0-value" with value "[Test Data] First sentence."
  And I click the "Save draft and continue editing" button
  Then I should see "1 error has been found: Body text"

  # Create the page with intention of using biography providing required fields.
  Given I fill in ckeditor "edit-field-body-0-value" with "[Test Data] Profile Body"
  And I click the "Save" button
  Then I should see "Staff Profile James Smith has been updated."

  # Check to see if a change of intention still allows data saving.
  Given I click the edit tab
  And I fill in ckeditor "edit-field-body-0-value" with "[Test Data] Fresh body."
  And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
  And I fill in field with selector "#edit-field-intro-text-0-value" with value "[Test Data] Better words."
  And I uncheck the "Create profile page with biography" checkbox
  And I click the "Save" button
  Then I should see "Staff Profile James Smith has been updated."
  And I should see "Better words."
  And I should see "Fresh body."
