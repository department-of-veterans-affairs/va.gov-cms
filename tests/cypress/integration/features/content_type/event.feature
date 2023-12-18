@content_type__event
Feature: Content Type: Event

  Scenario: Log in and create an event.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "event" node

  Scenario: Confirm that event location conditional fields are cleared out if parent options change
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/event"

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

    # Check the registration conditional fields.
    When I check the "Include registration information" checkbox
    And I scroll to element '#edit-field-include-registration-info-wrapper'
    And I select option "Register" from dropdown "Call to action"
    And I select option "URL" from dropdown "How to sign up"
    And I fill in field with selector "#edit-field-link-0-uri" with value "http://example.com"
    Then an element with the selector "#edit-field-cta-email-0-value" should be empty
    When I select option "Email" from dropdown "How to sign up"
    And I fill in field with selector "#edit-field-cta-email-0-value" with value "test@example.com"
    Then an element with the selector "#edit-field-link-0-uri" should be empty

  Scenario: Confirm that the default time zone when creating an event is set explicitly to Eastern.
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/event"
    Then the element with selector "#edit-field-datetime-range-timezone-0-timezone" should contain "New York"

  Scenario: Confirm that the event form conditional elements are shown or hidden appropriately
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/event"
    And I select the "At a VA facility" radio button
    Then I should see "Facility location"
    And I should see "Building, floor, or room"
    And I should not see "Street address"
    And I should not see an element with the selector "#edit-field-address-0-address-locality"
    And I should not see an element with the selector "#edit-field-address-0-address-administrative-area"
    And I should not see "Country"
    And I should not see an element with the selector "#edit-field-url-of-an-online-event-0-uri"

    # Location conditional fields.
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

    # Registration conditional fields.
    When I check the "Include registration information" checkbox
    And I scroll to element '#edit-field-include-registration-info-wrapper'
    Then "Cost" should be visible
    And I should see "Registration is required for this event"
    And "Call to action" should be visible
    And "How to sign up" should not be visible
    And I should not see an element with the selector "#edit-field-link-0-uri"
    And I should not see an element with the selector "#edit-field-cta-email-0-value"

    When I select option "Register" from dropdown "Call to action"
    Then I should see "How to sign up"
    And the option "- None -" from dropdown "How to sign up" should be selected

    When I select option "URL" from dropdown "How to sign up"
    Then I should see an element with the selector "#edit-field-link-0-uri"
    And I should not see an element with the selector "#edit-field-cta-email-0-value"

    When I select option "Email" from dropdown "How to sign up"
    Then I should see an element with the selector "#edit-field-cta-email-0-value"
    And I should not see an element with the selector "#edit-field-link-0-uri"

    When I select option "Apply" from dropdown "Call to action"
    Then I should see "How to sign up"
    And the option "- None -" from dropdown "How to sign up" should be selected

    When I select option "URL" from dropdown "How to sign up"
    Then I should see an element with the selector "#edit-field-link-0-uri"
    And I should not see an element with the selector "#edit-field-cta-email-0-value"

    When I select option "Email" from dropdown "How to sign up"
    Then I should see an element with the selector "#edit-field-cta-email-0-value"
    And I should not see an element with the selector "#edit-field-link-0-uri"

    When I select option "RSVP" from dropdown "Call to action"
    Then I should see "How to sign up"
    And the option "- None -" from dropdown "How to sign up" should be selected

    When I select option "URL" from dropdown "How to sign up"
    Then I should see an element with the selector "#edit-field-link-0-uri"
    And I should not see an element with the selector "#edit-field-cta-email-0-value"

    When I select option "Email" from dropdown "How to sign up"
    Then I should see an element with the selector "#edit-field-cta-email-0-value"
    And I should not see an element with the selector "#edit-field-link-0-uri"

    When I select option "More Details" from dropdown "Call to action"
    Then I should see "How to sign up"
    And the option "- None -" from dropdown "How to sign up" should be selected

    When I select option "URL" from dropdown "How to sign up"
    Then I should see an element with the selector "#edit-field-link-0-uri"
    And I should not see an element with the selector "#edit-field-cta-email-0-value"

    When I select option "Email" from dropdown "How to sign up"
    Then I should see an element with the selector "#edit-field-cta-email-0-value"
    And I should not see an element with the selector "#edit-field-link-0-uri"

    When I uncheck the "Include registration information" checkbox
    Then I should not see "Cost"
    And I should not see "Registration is required for this event"
    And I should not see "Call to action"
    And I should not see an element with the selector "#edit-field-link-0-uri"
    And "How to sign up" should not be visible
    And I should not see an element with the selector "#edit-field-cta-email-0-value"

  Scenario: Confirm tabledrag is not enabled on the event date field
    Given I am logged in as a user with the "content_admin" role
    When I am at "node/add/event"
    Then an element with the selector "#edit-field-datetime-range-timezone-wrapper button.tabledrag-toggle-weight" should not exist
