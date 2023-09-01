Feature: Configurable Pager Heading
  In order to ensure accessibility-friendly content views
  As an administrator
  I need to be able to change a view pager's heading element for a given view

  Scenario: The View Administrator should configure a view pager's heading element
    Given I am logged in as a user with the "administrator" role
    When I am at "/admin/structure/views/view/content"
    And I click the "Paged, 25 items" link
    And I select option "h2" from dropdown with selector "select[id^=edit-pager-options-pagination-heading-level]"
    And I click the "Apply" button
    And I click the "Save" button
    And I wait "3" seconds
    Then an element with the selector "h2[id^=pagination-heading--].visually-hidden" should exist
