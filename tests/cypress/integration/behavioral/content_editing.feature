@content_editing
Feature: CMS Users may effectively create & edit content
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and confirm that System-wide alerts can be created and edited
    When I am logged in as a user with the "content_admin" role

    # Create our initial draft
    # We need to target an existing node
    # ("Operating status - VA Pittsburgh health care")
    # to prevent unique validation failure.
    Then I am at "node/1010/edit"
    And I click the "Add new banner alert" button
    And I select option "Information" from dropdown "Alert type"
    And I fill in "Title" with "[Test Data] Alert Title"
    And I fill in ckeditor "field-body-0-value" with "[Test Data] Alert Body"
    And I click the "Create banner alert" button
    And I wait "5" seconds
    And I click the "Save draft and continue editing" button
    Then I should see "Pages for the following VAMC systems"
    And I should see "[Test Data] Alert Title"

  Scenario: Confirm that content cannot be published directly from the node view but can from the node edit form.
    Given I am logged in as a user with the "content_admin" role
    And I create a "landing_page" node
    Then I should not see an element with the selector "#edit-new-state"
    And I edit the node
    Then the element with selector "#edit-moderation-state-0-state" should contain "Draft"

  Scenario: Confirm Generate automatic URL alias is unchecked after node publish.
    When I am logged in as a user with the "administrator" role
    And I am at "node/add/landing_page"
    Then the "Generate automatic URL alias" checkbox should be checked
    And I create a "landing_page" node
    And I edit the node
    Then the "Generate automatic URL alias" checkbox should be checked
    And I publish the node
    And I edit the node
    Then the "Generate automatic URL alias" checkbox should not be checked

  Scenario: Confirm Generate automatic URL alias is unchecked after taxonomy term publish.
    Given I am logged in as a user with the "administrator" role
    And I am at "admin/structure/taxonomy/manage/health_care_service_taxonomy/add"
    Then the "Generate automatic URL alias" checkbox should be checked
    And I create a "health_care_service_taxonomy" taxonomy term
    And I edit the term
    And the "Generate automatic URL alias" checkbox should not be checked

  Scenario: Confirm that press release country fields are shown correctly
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/press_release"
    Then I should see "Create News Release"
    And the element with selector "#edit-field-address-0" should contain "Country"
    And the element with selector "#edit-field-address-0" should contain "City"
    And the element with selector "#edit-field-address-0" should contain "State"

  Scenario: Confirm that the default time zone when creating an event is set explicitly to Eastern.
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/event"
    And the element with selector "#edit-field-datetime-range-timezone-0-timezone" should contain "New York"

  Scenario: Log in, edit, and save nodes with save and continue button and confirm revision saves changes.
    When I am logged in as a user with the "administrator" role
    And I create a "checklist" node and continue

    # Verify data has been saved
    Then I should not see "error has been found:"
    And I should see "[Test Data]"
    And the element with selector "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" should have attribute "value" containing value "[Test Header Value]"
    And the element with selector "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-checklist-items-0-value" should have attribute "value" containing value "[Test Items Value]"
    And the option "VACO" from dropdown with selector "#edit-field-administration" should be selected

    # Make sure additional edits are saved
    And I fill in "Page title" with "[Test Data] Save and Continue Test"
    And I fill in field with selector "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" with value "[Test Items Value] Some item"
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
