@content_type__health_care_region_detail_page
Feature: Content Type: VAMC Detail Page

  @critical_path
  Scenario: Log in and create a VAMC Detail Page.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "health_care_region_detail_page" node
