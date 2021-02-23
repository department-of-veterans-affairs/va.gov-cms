Feature: Entity Reference Validation
  In order to confirm that entity reference fields are validated correctly
  As an editor
  Resources & Support content types should not allow duplicate refereneces

  @entity_reference_validation

  Scenario: Node edit forms should not have a visible What's New in the CMS block.
    Given I am logged in as a user with the "content_admin" role
    And I create a "step_by_step" node
    And I click the "Edit" link
    And I click the "Select Benefit Hub(s)" link
    And I check "VA Burials and memorials"
    And I click the "Select Benefit Hub(s)" link
    And I check "VA Burials and memorials"
    And I save the node
