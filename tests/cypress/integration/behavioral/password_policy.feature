Feature: Password policies are enforced
  In order to enhance security
  As anyone involved in the project
  I need to be required to set a strong password

  @password_policy
  Scenario: Strong passwords should not be required when changing a password on lower environments
    Given I disable the password policy
    When I am logged in as a user with the "content_admin" role and password "test"
    And I am at "/user"
    And I click the edit tab
    And I fill in "Current password" with "test"
    And I fill in "Password" with "1"
    And I fill in "Confirm password" with "1"
    And I wait "3" seconds
    And I save the user
    Then I should not see "password has a score"
    Then I should not see "but the policy requires a score"
    And I should see "The changes have been saved."

  @password_policy
  Scenario: Strong passwords should be required when changing a password on production environments
    Given I enable the password policy
    When I am logged in as a user with the "content_admin" role and password "test"
    And I am at "/user"
    And I click the edit tab
    And I fill in "Current password" with "test"
    And I fill in "Password" with "1"
    And I fill in "Confirm password" with "1"
    And I wait "1" seconds
    And I save the user
    Then I should see "password has a score"
    Then I should see "but the policy requires a score"
    And I should not see "The changes have been saved."
    Then I log out

    And I disable the password policy

