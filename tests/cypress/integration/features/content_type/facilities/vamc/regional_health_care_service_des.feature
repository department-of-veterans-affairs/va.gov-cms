@content_type__regional_health_care_service_des
Feature: CMS Users may effectively interact with the VAMC System Health Service form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  @critical_path
  Scenario: Log in and edit VAMC System Health Service as a Lovell editor
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "347"
    Then I am at "/node/52672/edit"
    Then I select option "Lovell Federal health care - TRICARE" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Lovell Federal health care - TRICARE" from dropdown with selector "#edit-field-region-page"
    Then I should not see an option with the text "Women Veteran care" from dropdown with selector "#edit-field-service-name-and-descripti"
    Then I select option "Lovell Federal health care - VA" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Lovell Federal health care - VA" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Women Veteran care" from dropdown with selector "#edit-field-service-name-and-descripti"

  # Lovell Federal umbrella test
    Then I scroll to position "bottom"
    Then an element with the selector "#edit-field-administration" should be visible
    And I should not see an option with the text "Lovell Federal health care" from dropdown with selector "#edit-field-administration"

  @critical_path
  Scenario: Log in and add a VAMC System Health Service as a Lovell editor
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "347"
    Then I am at "/node/add/regional_health_care_service_des"
    Then I select option "Lovell Federal health care - TRICARE" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Lovell Federal health care - TRICARE" from dropdown with selector "#edit-field-region-page"
    Then I should not see an option with the text "Women Veteran care" from dropdown with selector "#edit-field-service-name-and-descripti"
    Then I select option "Lovell Federal health care - VA" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Lovell Federal health care - VA" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Women Veteran care" from dropdown with selector "#edit-field-service-name-and-descripti"

  # Lovell Federal umbrella test
    Then I scroll to position "bottom"
    Then an element with the selector "#edit-field-administration" should be visible
    And I should not see an option with the text "Lovell Federal health care" from dropdown with selector "#edit-field-administration"

  @critical_path
  Scenario: Log in and edit VAMC System Health Service as a non-Lovell editor
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "372"
    Then I am at "/node/53057/edit"
    Then I select option "VA Alaska health care" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "VA Alaska health care" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Women Veteran care" from dropdown with selector "#edit-field-service-name-and-descripti"

  @critical_path
  Scenario: Log in and add a VAMC System Health Service as a non-Lovell editor
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "372"
    Then I am at "/node/add/regional_health_care_service_des"
    Then I select option "VA Alaska health care" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "VA Alaska health care" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Women Veteran care" from dropdown with selector "#edit-field-service-name-and-descripti"

  Scenario: Editors should not be able to rename a VAMC System Health Service
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "372"
    And I unlock node 53057
    Then I am at "node/53057/edit"
    Then an element with the selector 'select[data-drupal-selector^="edit-field-service-name-and-descripti"]' should be disabled

  Scenario: Administrators should be able to rename a VAMC System Health Service
    When I am logged in as a user with the roles "administrator"
    And I unlock node 53057
    Then I am at "node/53057/edit"
    Then an element with the selector 'select[data-drupal-selector^="edit-field-service-name-and-descripti"]' should not be disabled
    Then I scroll to position "bottom"
    And I click the "Unlock" link
    And I click the "Confirm break lock" button

  Scenario: Archiving a VAMC System Health Service should archive related facility services
    Given I am logged in as a user with the roles "vamc_content_creator, content_publisher, content_admin"
    And my workbench access sections are set to "372"
    And I unlock node 23940
    Then I am at "node/23940/edit"
    And I select option "Archived" from dropdown with selector "select#edit-moderation-state-0-state"
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I save the node
    Then I confirm the dialog with the text "By saving this System service as archived"
    Then I should see an element with the selector "article.node--unpublished.node--type-health-care-local-health-service"
