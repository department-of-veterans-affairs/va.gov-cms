@api @mock_va_gov_urls
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
    When I am logged in as a user with the "content_admin" role

    Given "topics" terms:
    | name            | parent | description | format     | language |
    | BeHat - Topic 1 |        |             | plain_text | UND      |
    | BeHat - Topic 2 |        |             | plain_text | UND      |
    | BeHat - Topic 3 |        |             | plain_text | UND      |
    | BeHat - Topic 4 |        |             | plain_text | UND      |

    # Create beneficiaries term.
    Given "audience_beneficiaries" terms:
    | name                     | parent | description | format     | language |
    | BeHat - Awesome Veterans |        |             | plain_text | UND      |

    # Create our initial draft
    Then I am at "node/add/checklist"
    And I fill in "Page title" with "Behat save and continue new test"
    And I fill in "#edit-field-primary-category" with the text "282"
    And I fill in "#edit-field-buttons-0-subform-field-button-label-0-value" with the text "test button label"
    And I fill in "#edit-field-buttons-0-subform-field-button-link-0-uri" with the text "<nolink>"
    And I fill in "#edit-field-checklist-0-subform-field-section-header-0-value" with the text "Behat save and continue new test section header 1"
    And I fill in "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-section-header-0-value" with the text "Behat save and continue new test section header 2"
    And I fill in "#edit-field-checklist-0-subform-field-checklist-sections-0-subform-field-checklist-items-0-value" with the text "Behat save and continue new test checklist item 1"
    And I fill in "#edit-field-administration" with the text "5"

    # Select four topics.
    And I check "BeHat - Topic 1"
    And I check "BeHat - Topic 2"
    And I check "BeHat - Topic 3"
    And I check "BeHat - Topic 4"

    # Also select an audience.
    And I select the "BeHat - Awesome Veterans" radio button
    And I press "Save draft and continue editing"

    # Confirm that our custom validation for Audiences & Topics is working.
    Then I should see "No more than 4 Topic/Audience tags may be selected"

@content_editing
  Scenario: Confirm that menu link functionality works correctly
    Given I am logged in as a user with the "content_admin" role
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

@content_editing
  Scenario: Confirm that the EWA block URL is shown correctly.
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/office"
    Then I should see "Create Office"

    # Create an office node.
    And I fill in "Name" with "Test Office - BeHaT"
    And I fill in "Meta title tag" with "Test Office - BeHaT | Veterans Affairs"
    And I fill in "Owner" with "5"
    And I press "Save"

    # Confirm that the va.gov url is not shown for nodes without a published revision.
    Then I should see "Content Type: Office" in the "#block-entitymetadisplay" element
    And I should not see "VA.gov URL" in the "#block-entitymetadisplay" element

    # Publish the node.
    And I select "Published" from "Change to"
    And I fill in "Log message" with "Test publishing"
    And I press "Apply"

    # Confirm that the va.gov url is shown for nodes with a published revision.
    Then I should see "The moderation state has been updated"
    And I should see "VA.gov URL" in the "#block-entitymetadisplay" element
    And I should not see "(pending)" in the "#block-entitymetadisplay" element

    # Confirm that the va.gov url is not clickable when updating a node.
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT"
    And I fill in "Name" with "Test Office - BeHaT 404"
    And I press "Save"
    Then I should see "VA.gov URL" in the "#block-entitymetadisplay" element

    # (Re-)Publish the node.
    And I select "Published" from "Change to"
    And I fill in "Log message" with "Test publishing"
    And I press "Apply"
    And I should see "(pending)" in the "#block-entitymetadisplay" element

    # Archive the node.
    Then I visit the "edit" page for a node with the title "Test Office - BeHaT 404"
    And I select "Archived" from "Change to"
    And I fill in "Revision log message" with "Test archiving"
    And I press "Save"
    Then I should see "Office Test Office - BeHaT 404 has been updated."
    And I should see "Content Type: Office" in the "#block-entitymetadisplay" element
    And I should not see "VA.gov URL" in the "#block-entitymetadisplay" element

@content_editing
  Scenario: Confirm that press release country fields are shown correctly
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/press_release"
    Then I should see "Create News Release"
    And I should see "Country" in the "#edit-field-address-0" element
    And I should see "City" in the "#edit-field-address-0" element
    And I should see "State" in the "#edit-field-address-0" element

@content_editing
  Scenario: Log in and confirm that System-wide alerts can be created and edited
    When I am logged in as a user with the "content_admin" role

    # Create our initial draft
    Then I am at "node/add/vamc_operating_status_and_alerts"
    And I press "Add new banner alert"
    And I select "Information" from "Alert type"
    And I fill in "Title" with "BeHat Alert title"
    And I fill in "Alert body" with "BeHat Alert body"
    And I press "Save draft and continue editing"
    Then I should see "Pages for the following VAMC systems"
    And I should not see "BeHat Alert Body"

    # Confirm that paragraph can be edited inline
    And I press "edit-field-banner-alert-entities-0-actions-ief-entity-edit"
    Then I should see "BeHat Alert Body"

@content_editing
  Scenario Outline: Confirm that content cannot be published directly from the node edit form but can from the node view.
    Given I am logged in as a user with the "content_admin" role
    And I am viewing an <type> with the title <title>
    Then the "#edit-new-state" element should exist
    Then I should see "published" in the "#edit-new-state" element    
    And I visit the "edit" page for a node with the title <title>
    Then "#edit-moderation-state-0-state" should not contain "published"
    Examples:
      | type                             | title                                 |
      | "page"                           | "page page"                           |
      | "landing_page"                   | "landing_page page"                   |
      | "health_care_region_detail_page" | "health_care_region_detail_page page" |
      | "event"                          | "event page"                          |
      | "event_listing"                  | "event_listing page"                  |
      | "health_care_local_facility"     | "health_care_local_facility page"     |
      | "health_care_region_page"        | "health_care_region_page page"        |
      | "press_release"                  | "press_release page"                  |
      | "outreach_asset"                 | "outreach_asset page"                 |
      | "publication_listing"            | "publication_listing page"            |
      | "news_story"                     | "news_story page"                     |
