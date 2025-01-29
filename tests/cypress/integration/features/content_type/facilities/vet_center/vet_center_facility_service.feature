@content_type__vet_center_facility_health_servi
Feature: CMS Users may effectively interact with the Vet Center - Facility Service form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

Scenario: Editors should not be able to rename a Vet Center - Facility Service
  Given I am logged in as a user with the roles "content_creator_vet_center, content_publisher, content_editor, content_admin"
  # Escanaba Vet Center
  And my workbench access sections are set to "392"
  # Escanaba Vet Center - Telehealth
  When I am at "node/17927/edit"
  And an element with the selector 'select[data-drupal-selector^="edit-field-service-name-and-descripti"]' should be disabled
  Then I scroll to position "bottom"
  And I click the "Unlock" link
  And I click the "Confirm break lock" button

Scenario: Editors should be able to archive an optional Vet Center - Facility Service
  Given I am logged in as a user with the roles "content_creator_vet_center, content_publisher, content_editor"
  # Escanaba Vet Center
  And my workbench access sections are set to "392"
  # Escanaba Vet Center - Workshops and Classes
  When I am at "node/19570/edit"
  And I scroll to element "select#edit-moderation-state-0-state"
  Then an option with the text "Archived" from dropdown with selector "select#edit-moderation-state-0-state" should be visible
  Then I scroll to position "bottom"
  And I click the "Unlock" link
  And I click the "Confirm break lock" button

Scenario: Editors should be not able to archive a required Vet Center - Facility Service
  Given I am logged in as a user with the roles "content_creator_vet_center, content_publisher, content_editor"
  # Escanaba Vet Center
  And my workbench access sections are set to "392"
  # Escanaba Vet Center - Addiction
  When I am at "node/17597/edit"
  And I scroll to element "select#edit-moderation-state-0-state"
  Then an option with the text "Archived" from dropdown with selector "select#edit-moderation-state-0-state" should not be visible
  Then I scroll to position "bottom"
  And I click the "Unlock" link
  And I click the "Confirm break lock" button

Scenario: Admins should be able to archive a required Vet Center - Facility Service
  Given I am logged in as a user with the "administrator" role
  # Escanaba Vet Center - Suicide prevention
  When I am at "node/17926/edit"
  And I scroll to element "select#edit-moderation-state-0-state"
  Then an option with the text "Archived" from dropdown with selector "select#edit-moderation-state-0-state" should be visible
  Then I scroll to position "bottom"
  And I click the "Unlock" link
  And I click the "Confirm break lock" button

Scenario: Administrators should be able to rename a Vet Center - Facility Service
  Given I am logged in as a user with the "administrator" role
  # Escanaba Vet Center - Telehealth
  When I am at "node/17927"
  Then I should see "Escanaba Vet Center - Telehealth"

  Then I click the edit tab
  Then an element with the selector 'select[data-drupal-selector^="edit-field-office"]' should not be disabled
  And an element with the selector 'select[data-drupal-selector^="edit-field-service-name-and-descripti"]' should not be disabled

  # Duluth Vet Center
  Then I select option '3751' from dropdown with selector 'select[data-drupal-selector^="edit-field-office"]'
  # Women Veteran Care
  And I select option '57' from dropdown with selector 'select[data-drupal-selector^="edit-field-service-name-and-descripti"]'
  And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
  And I save the node
  Then I should see "Duluth Vet Center - Women Veteran care"
