Feature: API

  Scenario: JSON API consumer can access route translation for a given vamc_system_medical_records_offi
    Given I am an anonymous user
    And I should receive status code 200 when I request "/router/translate-path?path=%2Fboston-health-care%2Fmedical-records-office%2F"
  
  Scenario: JSON API consumer can access vamc_system_medical_records_offi nodes
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/vamc_system_medical_records_offi?page[limit]=1&filter[status]=1&include=field_office"
  
  Scenario: JSON API consumer can access health_care_local_facility nodes to get facility ids for services
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/health_care_local_facility?page[limit]=1&filter[status]=1"
  
  Scenario: JSON API consumer can access vha_facility_nonclinical_service nodes by list of facility ids
    Given I am an anonymous user
    And I should receive status code 200 when I request "/jsonapi/node/vha_facility_nonclinical_service?page[limit]=1&filter[status]=1&filter[field_service_name_and_descripti.name]=Medical records&filter[field_facility_location.id][condition][path]=field_facility_location.id&filter[field_facility_location.id][condition][value][0]=e452dbe8-9c73-4bed-b9ea-06f9420db982&filter[field_facility_location.id][condition][value][1]=9759def1-4e36-4ec5-b741-6cc8b40884c5&filter[field_facility_location.id][condition][value][2]=61cc9edd-7155-4882-99ae-128ae0984df6&filter[field_facility_location.id][condition][value][3]=cb577a82-953f-4306-84c6-a65abf1b45ea&filter[field_facility_location.id][condition][value][4]=9bfd9427-3c57-4713-9b89-71b0d95ddada&filter[field_facility_location.id][condition][value][5]=f4f16d9e-9fe4-4be4-8492-48b3f6519f89&filter[field_facility_location.id][condition][value][6]=7bf97522-875d-4524-a208-6f4ba19a81aa&filter[field_facility_location.id][condition][value][7]=d811b929-f561-4128-9b30-1debfe3e6a4a&filter[field_facility_location.id][condition][operator]=IN&filter[field_facility_location.status]=1&include=field_facility_location,field_service_location,field_service_location.field_email_contacts,field_service_location.field_other_phone_numbers,field_service_location.field_service_location_address,field_service_location.field_phone"