Feature: Content Release
  In order to reliably and predictably release content
  As anyone involved in the project
  I need the content release page to manage and reflect an orderly underlying release process

  @skip_on_brd
  Scenario: The content release page should normally display no in-process releases
    Given I am logged in as a user with the "content_admin" role
    And I am at "/admin/content/deploy"
    Then I should see "Front end has not been built yet."
    When I click the "Release content" button

  @skip_on_brd @ignore
  Scenario: The content release page should show a pending default release initiated within the browser
    Given I am logged in as a user with the "content_admin" role
    And I initiate a content release
    Then I should see "Pending"

  @skip_on_brd @ignore
  Scenario: The content release page should show a pending chosen release initiated within the browser
    Given I am logged in as a user with the "content_admin" role
    And I initiate a content release with the branch "master"
    And I should see "Pending"

  @skip_on_brd @ignore
  Scenario: The content release page should show a pending default release initiated from the command line
    Given I am logged in as a user with the "content_admin" role
    And I initiate a content release from the command line
    And I reload the page
    Then I should see "Pending"

  @skip_on_brd @ignore
  Scenario: The content release page should show a pending chosen release initiated from the command line
    Given I am logged in as a user with the "content_admin" role
    And I initiate a content release from the command line with the branch "master"
    And I reload the page
    Then I should see "Branch: "
    And I should see "Pending"
