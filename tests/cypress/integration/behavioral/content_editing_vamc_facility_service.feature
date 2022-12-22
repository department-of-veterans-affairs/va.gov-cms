@content_editing_vamc_facility_service
Feature: CMS Users may effectively interact with the VAMC Facility Health Service form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and create VAMC Facility Health Service
    When I am logged in as a user with the "content_admin" role
    Then I am at "/node/add/health_care_local_health_service"

    # Non-Lovell test
    Then I select option "---VA Alaska health care" from dropdown "Section"
    Then I click the button with selector "#edit-group-health-service-and-facilit"
    Then I select option "Anchorage VA Medical Center | VA Alaska health care" from dropdown with selector "#edit-field-facility-location"
    Then I select option "Audiology and speech at VA Alaska health care" from dropdown with selector "#edit-field-regional-health-service"

    # Lovell TRICARE test
    Then I select option "----Lovell - TRICARE" from dropdown "Section"
    Then I select option "Captain James A. Lovell Federal Health Care Center | Lovell Federal TRICARE health care" from dropdown with selector "#edit-field-facility-location"
    Then I select option "Cardiology at Lovell Federal TRICARE health care" from dropdown with selector "#edit-field-regional-health-service"

    # Lovell VA test
    And I select option "----Lovell - VA" from dropdown "Section"
    Then I select option "Captain James A. Lovell Federal Health Care Center | Lovell Federal VA health care" from dropdown with selector "#edit-field-facility-location"
    Then I select option "Cardiology at Lovell Federal VA health care" from dropdown with selector "#edit-field-regional-health-service"

    # Lovell Federal umbrella test
    Then an option with the text "Lovell Federal health care" from dropdown with selector "#edit-field-administration" should not be visible
