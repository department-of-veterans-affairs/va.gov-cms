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
    And I save the node
    Then I should see "1 error has been found"
    Then I should see "The value Careers and employment"
