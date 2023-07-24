Feature: Content Release
  In order to reliably and predictably release content
  As anyone involved in the project
  I need the content release page to manage and reflect an orderly underlying release process

  @new_content_release
  Scenario: The user should be able to deploy a content release from the Simple form
    Given I am logged in as a user with the "content_admin" role
    When I am at "/admin/content/deploy/simple"
    And I stub form submission for the current page
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "1 error has been found"
    And I should see "You must confirm that you understand this implication."
    When I check the checkbox with selector "#edit-confirm"
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Content release requested successfully"
    And I should see "Build request skipped; form is under test."

  @new_content_release
  Scenario: The user should be able to deploy a content release from the Git form
    Given I am logged in as a user with the "content_admin" role
    When I am at "/admin/content/deploy/git"
    And I stub form submission for the current page
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Content release requested successfully"
    And I should see "Build request skipped; form is under test."
    And I should see "Reset frontend version skipped; form is under test."

  @new_content_release
  Scenario: The user should be able to deploy a content release from the Git form with a branch selected.
    Given I am logged in as a user with the "content_admin" role
    When I am at "/admin/content/deploy/git"
    And I stub form submission for the current page
    And I select the "Select a different frontend branch/pull request" radio button
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Invalid selection."
    When I fill in autocomplete field with selector "#edit-git-ref" with value "main"
    Then I should see "BRANCH main"
    When I fill in autocomplete field with selector "#edit-git-ref" with value "BRANCH main (main)"
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Content release requested successfully"
    And I should see "Build request skipped; form is under test."
    And I should see "Set frontend version skipped; form is under test."

  @new_content_release
  Scenario: The Simple content release form should not display the content release status block.
    Given I am logged in as a user with the "content_admin" role
    When I am at "/admin/content/deploy/simple"
    And I reload the page
    And I scroll to position "bottom"
    Then "Content release status" should not exist

  @new_content_release
  Scenario: The Git content release form should normally display no in-process releases by default.
    Given I am logged in as a user with the "content_admin" role
    When I am at "/admin/content/deploy/git"
    And I reload the page
    And I scroll to position "bottom"
    Then I should see "Ready"
    And I should see "View the full output of the last completed build process (including a broken link report)."

  @skip_on_brd @old_content_release
  # When we shift to the new content release system, this will be obsoleted by
  # the new content release tests above.
  Scenario: The content release page should normally display no in-process releases
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line
    When I am at "/admin/content/deploy"
    And I reload the page
    And I scroll to position "bottom"
    Then "Ready" should exist

  @skip_on_brd @old_content_release
  # When we shift to the new content release system, this will be obsoleted by
  # the new content release tests above.
  Scenario: The content release page should show a pending default release initiated within the browser
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line
    When I am at "/admin/content/deploy"
    And I click the "Release content" button
    And I process the content release queue
    And I reload the page
    And I scroll to position "bottom"
    Then "Preparing" should exist
    And I reset the content release state from the command line
    And I reload the page
    And I scroll to position "bottom"
    Then "Ready" should exist

  @skip_on_brd @old_content_release
  # When we shift to the new content release system, this will be obsoleted by
  # the new content release tests above.
  # This test concerns functionality that is currently broken or not available
  # in all environments, specifically interactions with the frontend git repo.
  Scenario: The content release page should show a pending chosen release initiated within the browser
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line
    When I initiate a content release with the branch "main"
    And I process the content release queue
    And I am at "/admin/content/deploy"
    And I scroll to position "bottom"
    Then "Preparing" should exist
    And I reset the content release state from the command line
    And I reload the page
    And I scroll to position "bottom"
    Then "Ready" should exist

  @skip_on_brd @old_content_release
  # When we shift to the new content release system, this will be obsoleted by
  # the new content release tests above.
  Scenario: The content release page should show a pending default release initiated from the command line
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line
    And I initiate a content release from the command line
    And I process the content release queue
    And I am at "/admin/content/deploy"
    And I scroll to position "bottom"
    Then "Preparing" should exist
    And I reset the content release state from the command line
    And I reload the page
    And I scroll to position "bottom"
    Then "Ready" should exist

  @skip_on_brd @ignore @old_content_release
  # When we shift to the new content release system, this will be obsoleted by
  # the new content release tests above.
  # This test concerns functionality that is currently not available,
  # specifically initiating releases from the command line with a specific
  # branch.
  # If this unavailable functionality is permanently removed, this test should
  # be as well.
  Scenario: The content release page should show a pending chosen release initiated from the command line
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line
    And I initiate a content release from the command line with the branch "main"
    And I process the content release queue
    And I reload the page
    And I scroll to position "bottom"
    Then "Branch: " should exist
    And "Preparing" should exist
    And I reset the content release state from the command line
    And I reload the page
    And I scroll to position "bottom"
    Then "Ready" should exist
