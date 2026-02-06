@content_type__health_care_region_detail_page
Feature: Content Type: VAMC Detail Page

  @critical_path
  Scenario: Log in and create a VAMC Detail Page.
    Given I am logged in as a user with the "content_admin" role
    Then I create a "health_care_region_detail_page" node

  Scenario: Accordions can't be created in featured content but can in main content
    When I am logged in as a user with the "content_admin" role
    And I create a "health_care_region_detail_page" node and continue
    And I click the button with selector "#field-featured-content-q-a-add-more"
    And I wait "5" seconds
    And I wait until element with selector "[data-drupal-selector='edit-field-featured-content-0-subform-field-answer-wrapper']" is visible
    Then "Add Accordion group" should not exist
    And I click the button with selector "#edit-field-content-block-add-more-browse"
    And I click the button with selector "[data-drupal-selector='edit-add-more-button-q-a-section']"
    Then I wait until element with selector "[value='Add Accordion group']" is visible