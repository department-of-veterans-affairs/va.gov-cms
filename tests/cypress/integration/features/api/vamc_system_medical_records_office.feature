Feature: API

  Scenario: JSON API consumer can fetch all related data for a given vamc_system_medical_records_office path
    Given I am an anonymous user
    And I can successfully follow the vamc_system_medical_records_office API flow for "/boston-health-care/medical-records-office/"