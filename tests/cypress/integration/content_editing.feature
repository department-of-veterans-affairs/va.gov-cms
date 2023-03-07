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
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I click the "Save draft and continue editing" button
    Then "Pages for the following VAMC systems" should exist
    And "[Test Data] Alert Title" should exist
    And I scroll to position "bottom"
    And I click the "Unlock" link
    And I click the "Confirm break lock" button

  Scenario: Confirm that content cannot be published directly from the node view but can from the node edit form.
    Given I am logged in as a user with the "content_admin" role
    And I create a "landing_page" node
    Then an element with the selector "#edit-new-state" should not exist
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

  # EVENT FORM SPECS

  Scenario: Confirm that the default time zone when creating an event is set explicitly to Eastern.
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/event"
    Then the element with selector "#edit-field-datetime-range-timezone-0-timezone" should contain "New York"

  Scenario: Confirm that the event form conditional elements are shown or hidden appropriately
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/event"

    And I select the "At a VA facility" radio button
    Then I should see "Facility location"
    And I should see "Building, floor, or room"
    And I should not see "Street address"
    And I should not see an element with the selector "#edit-field-address-0-address-locality"
    And I should not see an element with the selector "#edit-field-address-0-address-administrative-area"
    And I should not see "Country"
    And I should not see an element with the selector "#edit-field-url-of-an-online-event-0-uri"

    When I select the "At a non-VA location" radio button
    Then I should not see "Facility location"
    And I should see "Building, floor, or room"
    And I should see "Street address"
    And I should see an element with the selector "#edit-field-address-0-address-locality"
    And I should see an element with the selector "#edit-field-address-0-address-administrative-area"
    And I should see "Country"
    And I should not see an element with the selector "#edit-field-url-of-an-online-event-0-uri"

    When I select the "Online" radio button
    Then I should not see "Facility location"
    And I should not see "Building, floor, or room"
    And I should not see "Street address"
    And I should not see an element with the selector "#edit-field-address-0-address-locality"
    And I should not see an element with the selector "#edit-field-address-0-address-administrative-area"
    And I should not see "Country"
    And I should see an element with the selector "#edit-field-url-of-an-online-event-0-uri"

    # Registration checkbox reveals conditional form elements
    When I check the "Include registration information" checkbox
    Then "Cost" should be visible
    And I should see "Registration is required for this event"
    And "Call to action" should be visible

    When I select option "Register" from dropdown "Call to action"
    Then I should see an element with the selector "#edit-field-link-0-uri"
    And I select option "Apply" from dropdown "Call to action"
    Then I should see an element with the selector "#edit-field-link-0-uri"
    And I select option "RSVP" from dropdown "Call to action"
    Then I should see an element with the selector "#edit-field-link-0-uri"
    And I select option "More Details" from dropdown "Call to action"
    Then I should see an element with the selector "#edit-field-link-0-uri"
    And I select option "- None -" from dropdown "Call to action"
    Then I should not see an element with the selector "#edit-field-link-0-uri"

    When I uncheck the "Include registration information" checkbox
    Then I should not see "Cost"
    And I should not see "Registration is required for this event"
    And I should not see "Call to action"
    And I should not see an element with the selector "#edit-field-link-0-uri"

  Scenario: Confirm that event form conditional fields are cleared out if parent options change
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/event"

    # Check registration call to action conditional field
    When I select option "Register" from dropdown "Call to action"
    And I fill in autocomplete field with selector "#edit-field-url-of-an-online-event-0-uri" with value "/node/5016"
    And I select option "- None -" from dropdown "Call to action"
    And I select option "Register" from dropdown "Call to action"
    Then an element with the selector "#edit-field-url-of-an-online-event-0-uri" should be empty

    # Check the location type conditional fields
    When I select the "At a VA facility" radio button
    And I fill in autocomplete field with selector "#edit-field-facility-location-0-target-id" with value "Aberdeen VA Clinic | VA Sioux Falls health care (1111)"
    And I select the "At a non-VA location" radio button
    And I fill in field with selector "#edit-field-address-0-address-address-line1" with value "555 Test Street"
    And I fill in field with selector "#edit-field-address-0-address-locality" with value "Testville"
    And I select option "Alabama" from dropdown "State"
    And I select the "Online" radio button
    And I fill in autocomplete field with selector "#edit-field-url-of-an-online-event-0-uri" with value "https://va.gov"
    And I select the "At a VA facility" radio button
    Then an element with the selector "#edit-field-facility-location-0-target-id" should be empty
    When I select the "At a non-VA location" radio button
    Then an element with the selector "#edit-field-address-0-address-address-line1" should be empty
    And an element with the selector "#edit-field-address-0-address-locality" should be empty
    And the option "- None -" from dropdown "State" should be selected
    When I select the "Online" radio button
    Then an element with the selector "#edit-field-url-of-an-online-event-0-uri" should be empty
