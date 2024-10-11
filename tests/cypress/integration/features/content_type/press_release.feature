@content_type__press_release
Feature: Content Type: News Release

  Scenario: Confirm that press release country fields are shown correctly
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/press_release"
    Then I should see "Create News Release"
    And the element with selector "#edit-field-address-0" should contain "Country"
    And the element with selector "#edit-field-address-0" should contain "City"
    And the element with selector "#edit-field-address-0" should contain "State"

  Scenario: Log in and create a news release
    Given I am logged in as a user with the "content_admin" role
    Then I create a "press_release" node
