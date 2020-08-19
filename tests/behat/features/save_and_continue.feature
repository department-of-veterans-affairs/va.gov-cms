@api
Feature: Save and continue button works as expected.
  In order to confirm cms items are saved
  As anyone involved in the project
  I need to click save and continue and confirm revision saves.

@save_and_continue
  Scenario: Log in, edit, and save nodes with save and continue button and confirm revision saves changes.
    When I am logged in as a user with the "administrator" role
    And I am at "node/add/checklist"
    # Create our initial draft
    And I fill in "Page title" with "Behat save and continue new test"
    And I fill in "#edit-field-meta-title-0-value" with the text "test meta title"
    And I fill in "#edit-field-description-0-value" with the text "test meta description"
    And I fill in "#edit-field-buttons-0-subform-field-button-label-0-value" with the text "test button label"
    And I fill in "#edit-field-buttons-0-subform-field-button-link-0-uri" with the text "<nolink>"
    And I fill in "#edit-field-checklist-0-subform-field-section-header-0-value" with the text "Behat save and continue new test section header 1"
    And I fill in "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" with the text "Behat save and continue new test section header 2"
    And I fill in "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-checklist-items-0-value" with the text "Behat save and continue new test checklist item 1"
    And I fill in "#edit-field-administration" with the text "5"
    And I press "Save draft and continue editing"

    # Confirm our values
    Then I should not see "error has been found:"
    And I should see "Behat save and continue new test"
    And "#edit-field-meta-title-0-value" should have the "value" with "test meta title"
    And "#edit-field-description-0-value" should have the "value" with "test meta description"
    And "#edit-field-buttons-0-subform-field-button-label-0-value" should have the "value" with "test button label"
    And "#edit-field-buttons-0-subform-field-button-link-0-uri" should have the "value" with "<nolink>"
    And "#edit-field-checklist-0-subform-field-section-header-0-value" should have the "value" with "Behat save and continue new test section header 1"
    And "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" should have the "value" with "Behat save and continue new test section header 2"
    And "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-checklist-items-0-value" should have the "value" with "Behat save and continue new test checklist item 1"
    And the "5" option from "#edit-field-administration" should be selected

    # Make sure additional edits are saved
    And I fill in "Page title" with "Behat save and continue new test - edited"
    And I fill in "#edit-field-description-0-value" with the text "test meta description - edited"
    And I fill in "#edit-field-buttons-0-subform-field-button-label-0-value" with the text "test button label - edited"
    And I fill in "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" with the text "Behat save and continue new test section header 2 - edited"
    And I press "Save draft and continue editing"
    Then I visit the "" page for a node with the title "Behat save and continue new test - edited"
    Then I should see "Behat save and continue new test - edited"
    Then I should see "test meta title"
    Then I should see "test meta description - edited"
    Then I should see "test button label - edited"
    Then I should see "Behat save and continue new test section header 1"
    Then I should see "Behat save and continue new test section header 2 - edited"
    Then I should see "Behat save and continue new test checklist item 1"
    Then I visit the "edit" page for a node with the title "Behat save and continue new test - edited"

    # Make sure we are in draft state
    Then the "draft" option from "#edit-moderation-state-0-state" should be selected
