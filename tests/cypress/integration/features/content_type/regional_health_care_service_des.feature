@content_type__regional_health_care_service_des
Feature: CMS Users may effectively interact with the VAMC System Health Service form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

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

  Scenario: Log in and edit VAMC System Health Service as a non-Lovell editor
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "372"
    Then I am at "/node/53057/edit"
    Then I select option "VA Alaska health care" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "VA Alaska health care" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Women Veteran care" from dropdown with selector "#edit-field-service-name-and-descripti"

  Scenario: Log in and add a VAMC System Health Service as a non-Lovell editor
    When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
    And my workbench access sections are set to "372"
    Then I am at "/node/add/regional_health_care_service_des"
    Then I select option "VA Alaska health care" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "VA Alaska health care" from dropdown with selector "#edit-field-region-page"
    Then I should see an option with the text "Women Veteran care" from dropdown with selector "#edit-field-service-name-and-descripti"
