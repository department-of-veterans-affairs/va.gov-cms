@content_type__event
Feature: Content Type: Event

  Scenario: Log in and create an event.
    Given I am logged in as a user with the "administrator" role
    When I set the "feature_event_outreach_checkbox" feature toggle to "on"
    And I log out
    Given I am logged in as a user with the "content_admin" role
    And I create a "event" node

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

  Scenario: Users who can only publish to National Outreach Calendar do not see the "Publish to the National Outreach Calendar" checkbox
    Given I am logged in as a user with the "administrator" role
    When I set the "feature_event_outreach_checkbox" feature toggle to "on"
    And I log out
    Given I am logged in as a user with the roles "office_content_creator, content_publisher"
    And my workbench access sections are set to "7"
    And I am at "node/add/event"
    Then I should see "This event will automatically be published to the National Outreach Calendar"
