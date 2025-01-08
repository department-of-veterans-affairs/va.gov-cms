@content_type__checklist
Feature: Content Type: Checklist

  @critical_path
  Scenario: Log in and create a checklist.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "checklist" node

  Scenario: Log in, edit, and save nodes with save and continue button and confirm revision saves changes.
    When I am logged in as a user with the "administrator" role
    And I create a "checklist" node and continue
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    # Verify data has been saved
    Then "error has been found:" should not exist
    And I should see "[Test Data]"
    And the element with selector "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" should have attribute "value" containing value "[Test Header Value]"
    And the element with selector "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-checklist-items-0-value" should have attribute "value" containing value "[Test Items Value]"
    And the option "VACO" from dropdown with selector "#edit-field-administration" should be selected
    And an element with the selector "#edit-field-contact-information-0-top-links-remove-button" should not exist

    # Make sure additional edits are saved
    And I fill in "Page title" with "[Test Data] Save and Continue Test"
    And I fill in field with selector "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" with value "[Test Items Value] Some item"
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I click the "Save draft and continue editing" button

    # Confirm that the correct values are shown on preview.
    Then I visit the node
    Then I should see "[Test Data] Save and Continue Test"
    And I should see "[Test Items Value] Some item"

    # Confirm that the moderation history and state are shown correctly.
    And the element with selector ".views-field-moderation-state" should contain "Draft"
    Then I view the moderation history for the node
    And the element with selector ".views-field-moderation-state" should contain "Set to Draft on "

    # Make sure we are in draft state
    Then I edit the node
    And the option "Draft" from dropdown with selector "#edit-moderation-state-0-state" should be selected
