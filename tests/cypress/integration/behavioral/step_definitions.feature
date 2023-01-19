@step_definitions
Feature: Step definitions function as expected
  In order to ensure reliable behavioral tests
  As a strong beautiful software quality engineer
  I need my step definitions validated

  @ignore
  Scenario: I trigger a content release
    Given I trigger a content release

  Scenario: I select the radio button
    Given I am logged in as a user with the "administrator" role
    And I am at "/admin/content-models/users"
    And I select the "Visitors" radio button
    Then the "Visitors" radio button should be selected
    When I select the "Administrators only" radio button
    Then the "Administrators only" radio button should be selected

  Scenario: I create a taxonomy term
    Given I am logged in as a user with the "administrator" role
    And I create a "products" taxonomy term
    Then I should see "Created new term"

  Scenario: I should see xpath
    Given I am logged in as a user with the "administrator" role
    And I am at "/"
    Then I should see an element with the xpath "//body"
    Then I should not see an element with the xpath "//something-that-doesnt-exist"

  Scenario: The element with selector should have attribute
    Given I am logged in as a user with the "administrator" role
    And I am at "/"
    Then the element with selector "body" should have attribute "class"
    Then the element with selector "body" should not have attribute "nonsensical-attribute"
    Then the element with selector "body" should have attribute "data-once" with value "contextualToolbar-init big-pipe-early-behaviors"
    Then the element with selector "body" should have attribute "data-once" containing value "init"
    Then the element with selector "body" should have attribute "data-once" matching expression "con([ext]+)...Toolbar-[a-z]{4}"

  Scenario: I visit the node
    Given I am logged in as a user with the "administrator" role
    And I create a "step_by_step" node
    And I set the status of the node to "Published"

  Scenario: I fill in field with selector with value
    Given I am logged in as a user with the "administrator" role
    And I create a "step_by_step" node
    And I edit the node
    And I fill in field with selector "#edit-title-0-value" with value "Field retrieved via selector"

  Scenario: Selector should contain string
    Given I am logged in as a user with the "administrator" role
    And I am at "/admin/reports/dblog"
    Then the element with selector "h1.page-title" should contain "Recent log messages"
