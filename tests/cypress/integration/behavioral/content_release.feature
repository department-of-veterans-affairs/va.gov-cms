Feature: Content Release
  In order to reliably and predictably release content
  As anyone involved in the project
  I need the content release page to manage and reflect an orderly underlying release process

  @skip_on_brd
  Scenario: The content release page should normally display no in-process releases
    Given I am logged in as a user with the "content_admin" role
    When I am at "/admin/content/deploy"
    Then I should see "Front end has not been built yet."

  @skip_on_brd
  Scenario: The content release page should show a pending default release initiated within the browser
    Given I am logged in as a user with the "content_admin" role
    When I am at "/admin/content/deploy"
    And I click the "Release content" button
    And I process the content release queue
    And I reload the page
    Then I should see "Preparing"
    And I reset the content release state from the command line
    And I reload the page
    Then I should see "Ready"

  @skip_on_brd
  # This test concerns functionality that is currently broken or not available
  # in all environments, specifically interactions with the frontend git repo.
  Scenario: The content release page should show a pending chosen release initiated within the browser
    Given I am logged in as a user with the "content_admin" role
    When I initiate a content release with the branch "main"
    And I process the content release queue
    And I am at "/admin/content/deploy"
    Then I should see "Preparing"
    And I reset the content release state from the command line
    And I reload the page
    Then I should see "Ready"

  @skip_on_brd
  Scenario: The content release page should show a pending default release initiated from the command line
    Given I am logged in as a user with the "content_admin" role
    And I initiate a content release from the command line
    And I process the content release queue
    And I am at "/admin/content/deploy"
    Then I should see "Preparing"
    And I reset the content release state from the command line
    And I reload the page
    Then I should see "Ready"

  @skip_on_brd @ignore
  # This test concerns functionality that is currently not available,
  # specifically initiating releases from the command line with a specific
  # branch.
  # If this unavailable functionality is permanently removed, this test should
  # be as well.
  Scenario: The content release page should show a pending chosen release initiated from the command line
    Given I am logged in as a user with the "content_admin" role
    And I initiate a content release from the command line with the branch "main"
    And I process the content release queue
    And I reload the page
    Then I should see "Branch: "
    And I should see "Preparing"
    And I reset the content release state from the command line
    And I reload the page
    Then I should see "Ready"
