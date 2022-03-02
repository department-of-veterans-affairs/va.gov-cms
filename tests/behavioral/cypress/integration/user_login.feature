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
    Then I should be at "/section/vha/vamc-facilities/va-bedford-health-care"

  @piv
  Scenario: The homepage is the PIV enabled login form and the site title is as intended
    Given I am at "/"
    Then I should see "VA.gov | Content Management System"
    And I should see "Create and publish Veteran-centered content"
    And I should see "Log in with PIV" in the "#edit-link" element
    When I click on the text "Developer log in"
    Then I should see "Username"
    Then I should not see "Log in with PIV"

  @no_piv
  Scenario: The homepage is the login form without PIV and the site title is as intended
    Given I am at "/"
    Then I should see "VA.gov | Content Management System"
    And I should see "Create and publish Veteran-centered content"
    And I should see "Username"
    And I should not see "Developer log in"
    And I should not see "Log in with PIV"
    Then I should not see "Log in with PIV"
