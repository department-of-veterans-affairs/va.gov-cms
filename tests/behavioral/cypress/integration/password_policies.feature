Feature: Password policies are enforced
  In order to enhance security
  As anyone involved in the project
  I need to be required to set a strong password

  @password_strength
  Scenario: Strong passwords should be required when changing a password
    Given I am logged in as a user with the "content_admin" role and password "test"
    And I am at "/user"
    And I click the edit tab
    And I fill in "Current password" with "test"
    And I fill in "Password" with "1"
    And I fill in "Confirm password" with "1"
    Then I should see "Fail - The password has a score"
    Then I should see "but the policy requires a score"
    And I wait "1" seconds
    And I save the user
    And I should not see "The changes have been saved."
