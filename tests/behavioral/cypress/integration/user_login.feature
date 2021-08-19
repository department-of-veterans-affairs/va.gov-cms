@user_login
Feature: User Login
  As a CMS editor,
  I want to be able to log in
  So I can work

  Scenario: Redirect single-section users to their section on login
    Given I am logged in as a user with the "content_publisher" role
    And my workbench access sections are set to "204"
    And I log out
    And I log back in
    Then I should see "VA Bedford health care"
