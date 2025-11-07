@content_type__vha_facility_nonclinical_service
Feature: CMS Users may effectively interact with the VAMC Facility Non-clinical Service form
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

@critical_path
Scenario: Login and confirm that saving a VAMC Nonclinical Service works as expected.
  Given I am logged in as a user with the "content_admin" role
  And I unlock node 52453
  # Billing and insurance - Buffalo VA Medical Center

  When I am at "node/52453/edit"
  And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
  And I save the node
  Then I should see "Address"
  And I should see "Hours"
  And I should see "Contact info"
  And "Service Options" should not exist
  And "Appointments" should not exist
