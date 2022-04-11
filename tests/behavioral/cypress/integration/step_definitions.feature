@step_definitions
Feature: Step definitions function as expected
  In order to ensure reliable behavioral tests
  As a strong beautiful software quality engineer
  I need my step definitions validated

  @ignore
  Scenario: I select the radio button
    Given I am logged in as a user with the "administrator" role
    And I am at "/admin/content-models/users"
    And I select the "Visitors" radio button
    Then the "Visitors" radio button should be selected
    When I select the "Administrators only" radio button
    Then the "Administrators only" radio button should be selected

  @ignore
  Scenario: I trigger a content release
    Given I trigger a content release

  @ignore
  Scenario: I create a taxonomy term
    Given I am logged in as a user with the "administrator" role
    And I create a "products" taxonomy term
    Then I should see "Created new term"

  @ignore
  Scenario: I should see xpath
    Given I am logged in as a user with the "administrator" role
    And I am at "/"
    Then I should see xpath "//body"
    Then I should not see xpath "//something-that-doesnt-exist"

  @ignore
  Scenario: The element should have attribute
    Given I am logged in as a user with the "administrator" role
    And I am at "/"
    Then the element "body" should have attribute "class"
    Then the element "body" should not have attribute "nonsensical-attribute"
    Then the element "body" should have attribute "data-once" with value "contextualToolbar-init"
    Then the element "body" should have attribute "data-once" containing value "init"
    Then the element "body" should have attribute "data-once" matching expression "con([ext]+)...Toolbar-[a-z]{4}"

  Scenario: I visit the node
    Given I am logged in as a user with the "administrator" role
    And I create a "step_by_step" node
    And I set the status of the node to "Published"
