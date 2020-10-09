@api
Feature: CMS Users may effectively create & edit content
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

@content_editing
  Scenario: Log in and confirm that "Step by step" node edit has the correct field settings
    Given I am logged in as a user with the "administrator" role
    And I am at "node/add/step_by_step"
    Then I should see "Create Step-by-Step"
    And I should see "Add Step"

    # Confirm that the wysiwyg is present for the "Step" paragraph type.
    And the "#edit-field-steps-0-subform-field-step-0-subform-field-wysiwyg-wrapper" element should exist

    # Confirm that text format selection is not allowed for the "Step" paragraph type.
    And I should not see "Rich Text" in the "#edit-field-steps-0-subform-field-step-0-subform" element
    And I should not see "Plain text" in the "#edit-field-steps-0-subform-field-step-0-subform" element
    And the "#edit-field-steps-0-subform-field-step-0-subform-field-wysiwyg-0-format--2" element should not exist

    # Confirm that a user may add unlimited Step-by-step fields.
    And the "#edit-field-steps-add-more-add-more-button-step-by-step" element should exist

@content_editing
  Scenario: Log in and confirm that "Checklist" node edit has the correct field settings
    When I am logged in as a user with the "administrator" role
    And I am at "node/add/checklist"
    # Create our initial draft
    And I fill in "Page title" with "Behat save and continue new test"
    And I fill in "#edit-field-meta-title-0-value" with the text "test meta title"
    And I fill in "#edit-field-description-0-value" with the text "test meta description"
    And I fill in "#edit-field-primary-category" with the text "282"
    And I fill in "#edit-field-buttons-0-subform-field-button-label-0-value" with the text "test button label"
    And I fill in "#edit-field-buttons-0-subform-field-button-link-0-uri" with the text "<nolink>"
    And I fill in "#edit-field-checklist-0-subform-field-section-header-0-value" with the text "Behat save and continue new test section header 1"
    And I fill in "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" with the text "Behat save and continue new test section header 2"
    And I fill in "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-checklist-items-0-value" with the text "Behat save and continue new test checklist item 1"
    And I fill in "#edit-field-administration" with the text "5"

    # Select three topics.
    And I check "claims and appeals status"
    And I check "payments and debt"
    And I check "sign in"

    # Also select an audience.
    And I select "268" from "field_tags[0][subform][field_audience_beneficiares]"
    And I press "Save draft and continue editing"

    # Confirm that our custom validation for Audiences & Topics is working.
    # Then I should see "No more than 4 Topic/Audience tags may be selected"

@content_editing
  Scenario: Confirm that menu link functionality works correctly
    Given I am logged in as a user with the "administrator" role
    And I am at "node/add/office"
    Then I should see "Create Office"

    # Create an office node with a menu link.
    And I fill in "Name" with "Test Office - BeHaT"
    And I fill in "Meta title tag" with "Test Office - BeHaT | Veterans Affairs"
    And I fill in "Owner" with "5"
    And I check "Provide a menu link"
    And I fill in "Menu link title" with "Test Office - BeHat"
    And I uncheck "Enable in menu"
    And I press "Save"
    Then I should see "Test Office - BeHaT"
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT"
    And the "menu[link_enabled]" checkbox should not be checked

    # Verify that the menu link was created correctly.
    And I visit "admin/structure/menu/manage/outreach-and-events"
    Then I should see "Test Office - BeHaT (disabled)"

    # Enable the menu item.
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT"
    And the "menu[link_enabled]" checkbox should not be checked
    Then I check "Enable in menu"
    And I press "Save"

    # Verify that the menu link was updated correctly.
    And I visit "admin/structure/menu/manage/outreach-and-events"
    Then I should see "Test Office - BeHaT"
    And I should not see "Test Office - BeHaT (disabled)"
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT"
    And the "menu[link_enabled]" checkbox should be checked

