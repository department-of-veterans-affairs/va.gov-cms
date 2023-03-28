@content_editing_vamc_facility_service
Feature: CMS Users may effectively interact with the VAMC Facility Health Service form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

Scenario: Log in and create VAMC Facility Health Service as a Lovell editor
  When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
  And my workbench access sections are set to "347"
  Then I am at "/node/add/health_care_local_health_service"

# Lovell VA test
  And I select option "----Lovell - VA" from dropdown "Section"
  And I wait "2" seconds
  Then I select option "Captain James A. Lovell Federal Health Care Center | Lovell Federal health care - VA" from dropdown with selector "#edit-field-facility-location"
  Then I select option "Cardiology at Lovell Federal health care - VA" from dropdown with selector "#edit-field-regional-health-service"

# Lovell TRICARE test
  Then I select option "----Lovell - TRICARE" from dropdown "Section"
  And I wait "2" seconds
  Then I select option "Captain James A. Lovell Federal Health Care Center | Lovell Federal health care - TRICARE" from dropdown with selector "#edit-field-facility-location"
  Then I select option "Cardiology at Lovell Federal health care - TRICARE" from dropdown with selector "#edit-field-regional-health-service"

# Phone number AJAX test
  Then I click the "Add new phone number" button
  And I wait "20" seconds
  Then I should see an element with the selector "[data-drupal-selector*='edit-field-phone-numbers-paragraph-form-']"

# Lovell Federal umbrella test
  Then I scroll to position "bottom"
  Then an element with the selector "#edit-field-administration" should be visible
  And I should not see an option with the text "Lovell Federal health care" from dropdown with selector "#edit-field-administration"

Scenario: Log in and create VAMC Facility Health Service as a non-Lovell editor
  When I am logged in as a user with the roles "vamc_content_creator, content_publisher"
  And my workbench access sections are set to "205"
  Then I am at "/node/add/health_care_local_health_service"

  # Non-Lovell test
  Then I select option "---VA Boston health care" from dropdown "Section"
  And I wait "2" seconds
  Then I click the button with selector "#edit-group-health-service-and-facilit"
  Then I select option "Brockton VA Medical Center | VA Boston health care" from dropdown with selector "#edit-field-facility-location"
  Then I select option "Audiology and speech at VA Boston health care" from dropdown with selector "#edit-field-regional-health-service"
