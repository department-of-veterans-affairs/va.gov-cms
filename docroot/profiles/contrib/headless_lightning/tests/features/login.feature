@api @headless
Feature: User login

  Scenario: Redirecting users to a login form from a 403 response
    Given I am an anonymous user
    When I go to "/admin/content"
    Then the response status code should be 403
    And I should see a "Username" field
    And I should see a "Password" field

  Scenario: Logging in from a 403 response should send the user to the page they were trying to access
    Given I am an anonymous user
    When I go to "/admin/content"
    And I enter "admin" for "Username"
    And I enter "admin" for "Password"
    And I press "Log in"
    Then I should be on "/user/1/moderation/dashboard"
    And I should not see a "Username" field
    And I should not see a "Password" field
    And the response status code should be 200
