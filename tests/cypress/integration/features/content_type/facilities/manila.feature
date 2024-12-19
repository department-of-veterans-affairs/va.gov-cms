@content_type__event, @content_editing_vamc_facility
Feature: Content Types: Event, VAMC Facility

Scenario: Log in and create Event as a Manila editor
  When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
  And my workbench access sections are set to "1187"
  Then I am at "/node/add/event"
  And I select option "---Manila VA Clinic" from dropdown "Section"
  And I fill in "Name" with "[TEST] Manila event"
  And I fill in field with selector "#edit-field-datetime-range-timezone-0-time-wrapper-value-date" with value "2026-11-04"
  And I fill in field with selector "#edit-field-datetime-range-timezone-0-time-wrapper-end-value-date" with value "2026-11-04"
  And I fill in field with selector "#edit-field-datetime-range-timezone-0-time-wrapper-value-time" with value "10:00:00"
  And I fill in field with selector "#edit-field-datetime-range-timezone-0-time-wrapper-end-value-time" with value "11:00:00"
  And I select option "Manila" from dropdown "Time zone"
  And I select option "Manila VA Clinic: Events" from dropdown "Where should the event be listed?"
  And I fill in autocomplete field with selector "#edit-field-facility-location-0-target-id" with value "Manila VA Clinic | Manila VA Clinic (1059)"
  And I select option "Published" from dropdown "Save as"
  And I fill in "Revision log message" with "[TEST] Revision log message"
  And I click the "Save" button
  Then I should be at "manila-va-clinic"

Scenario: Log in and edit the Manila VA Clinic
  When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
  And my workbench access sections are set to "1187"
  Then I am at "/node/1059/edit"
  And I select option "Published" from dropdown "Save as"
  And I fill in "Revision log message" with "[TEST] Revision log message"
  And I click the "Save" button
  Then I should not be at "/manila-va-clinic/locations/manila-va-clinic"