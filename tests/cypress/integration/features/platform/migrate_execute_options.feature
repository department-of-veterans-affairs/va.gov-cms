Feature: Migrate Tools Execute Additional Options
  In order to ensure proper migration of content
  As an administrator
  I should be able to execute migrations using the UI with additional options

  Scenario: Admins should be able to list facility api ids in the idlist input
    Given I am logged in as a user with the "administrator" role
    And I am at "/admin/structure/migrate/manage/facility/migrations/va_node_facility_vet_centers/execute"
    And I click to expand "Additional execution options"
    And I fill in field with selector "#edit-idlist" with value "vc_0723V"
    When I check the checkbox with selector "#edit-update"
    Then an element with the selector ".form-item--error-message" should not exist
