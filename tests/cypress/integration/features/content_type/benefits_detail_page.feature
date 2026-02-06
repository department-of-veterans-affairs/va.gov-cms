@content_type__benefits_detail_page
Feature: Content Type: Benefits Detail Page

  @critical_path
  Scenario: Log in and create a benefits detail page
    Given I am logged in as a user with the "content_admin" role
    Then I create a "page" node

  Scenario: Accordions can't be created in featured content
    When I am logged in as a user with the "content_admin" role
    And I create a "page" node and continue
    And I click the button with selector "#field-featured-content-q-a-add-more"
    Then "Add Accordion group" should not exist

  Scenario: Accordions can be created in main content
    When I am logged in as a user with the "content_admin" role
    And I create a "page" node and continue
    And I click the button with selector "#edit-field-content-block-add-more-browse"
    Then I click the "Page-Specific Q&A Group" link
    Then I wait until I see "Add Accordion group"
