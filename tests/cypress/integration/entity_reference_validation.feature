Feature: Entity Reference Validation
  In order to confirm that entity reference fields are validated correctly
  As an editor
  I need Resources & Support content types to not allow duplicate references

  @entity_reference_validation
  Scenario: Duplicate Benefit Hub references should not be allowed
    Given I am logged in as a user with the "content_admin" role
    And I create a "step_by_step" node
    And I click the edit tab
    And I select the "VA Careers and employment" benefits hub
    And I wait "3" seconds
    And I select the "VA Careers and employment" benefits hub
    And I wait "3" seconds
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I save the node
    Then "1 error has been found" should exist
    Then "The value Careers and employment" should exist
