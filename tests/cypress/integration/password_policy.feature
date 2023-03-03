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
    Then "password has a score" should not exist
    Then "but the policy requires a score" should not exist
    And "The changes have been saved." should exist

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
    Then "password has a score" should exist
    Then "but the policy requires a score" should exist
    And "The changes have been saved." should not exist
    Then I log out

    And I disable the password policy

