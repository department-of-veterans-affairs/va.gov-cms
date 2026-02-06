@content_type__health_care_region_detail_page
Feature: Content Type: VAMC Detail Page

  @critical_path
  Scenario: Log in and create a VAMC Detail Page.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "health_care_region_detail_page" node

  Scenario: Accordions can't be created in featured content
    When I am logged in as a user with the "content_admin" role
    And I create a "health_care_region_detail_page" node and continue
    And I click the button with selector "#field-featured-content-q-a-add-more"
    Then "Add Accordion group" should not exist