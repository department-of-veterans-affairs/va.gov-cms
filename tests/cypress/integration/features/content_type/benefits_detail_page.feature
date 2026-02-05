@content_type__benefits_detail_page
Feature: Content Type: Benefits Detail Page

  @critical_path
  Scenario: Log in and create a benefits detail page
    Given I am logged in as a user with the "content_admin" role
    Then I create a "page" node

  Scenario: Accordions can't be created in featured content
    When I am logged in as a user with the "content_admin" role
    And I create a "page" node and continue
    And I click the element with selector "#field-featured-content-q-a-add-more"
    Then I should not see "Add Accordion group"
    