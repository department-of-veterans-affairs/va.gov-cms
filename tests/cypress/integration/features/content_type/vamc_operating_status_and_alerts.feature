@content_type__vamc_operating_status_and_alerts
Feature: Content Type: VAMC Operating Status

  Scenario: Log in and confirm that System-wide alerts can be created and edited
    Given I am logged in as a user with the "content_admin" role
    And I unlock node 1010

    # Create our initial draft
    # We need to target an existing node
    # ("Operating status - VA Pittsburgh health care")
    # to prevent unique validation failure.
    When I am at "node/1010/edit"
    And I click the "Add new banner alert" button
    And I wait for an element with the selector 'select[data-drupal-selector^="edit-field-banner-alert-form"]' to exist
    When I scroll to element 'select[data-drupal-selector^="edit-field-banner-alert-form"]'
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
