Feature: Content Release
  In order to reliably and predictably release content
  As anyone involved in the project
  I need the content release page to manage and reflect an orderly underlying release process

  @new_content_release @critical_path
  Scenario: The user should be able to deploy a content release from the Simple form
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line

    # Confirm and release.
    When I am at "/admin/content/deploy/simple"
    And I stub form submission for the current page
    And I check the checkbox with selector "#edit-confirm"
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Content release requested successfully"
    And I should see "Build request skipped; form is under test."

  @new_content_release @critical_path
  Scenario: The user should be able to deploy a content release from the Git form
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line

    # Default content release.
    When I am at "/admin/content/deploy/git"
    And I stub form submission for the current page
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Content release requested successfully"
    And I should see "Build request skipped; form is under test."
    And I should see "Reset content_build version skipped; form is under test."
    And I should see "Reset vets_website version skipped; form is under test."

  @new_content_release @critical_path
  Scenario: The user should be able to deploy a content release from the Git form with a content-build branch selected.
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line

    # Require that we select a content-build branch.
    When I am at "/admin/content/deploy/git"
    And I stub form submission for the current page
    And I select the "Select a different content-build branch/pull request" radio button
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Invalid selection."

    # Select content-build branch.
    When I fill in autocomplete field with selector "#edit-content-build-git-ref" with value "main"
    Then I should see "BRANCH main"
    When I fill in autocomplete field with selector "#edit-content-build-git-ref" with value "BRANCH main (main)"
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Content release requested successfully"
    And I should see "Build request skipped; form is under test."
    And I should see "Set content_build version skipped; form is under test."
    And I should see "Reset vets_website version skipped; form is under test."

  @new_content_release @critical_path
  Scenario: The user should be able to deploy a content release from the Git form with a vets-website branch selected.
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line

    # Require that we select a vets-website branch.
    When I am at "/admin/content/deploy/git"
    And I stub form submission for the current page
    And I select the "Select a different vets-website branch/pull request" radio button
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Invalid selection."

    # Select vets-website branch.
    When I fill in autocomplete field with selector "#edit-vets-website-git-ref" with value "main"
    Then I should see "BRANCH main"
    When I fill in autocomplete field with selector "#edit-vets-website-git-ref" with value "BRANCH main (main)"
    And I click the "Release content" button
    And I wait for form submission
    Then I should see "Content release requested successfully"
    And I should see "Build request skipped; form is under test."
    And I should see "Reset content_build version skipped; form is under test."
    And I should see "Set vets_website version skipped; form is under test."

  @new_content_release @critical_path
  Scenario: The user should be able to deploy a content release from the Git form with a content-build and vets-website branch selected.
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line

    # Basic form setup.
    When I am at "/admin/content/deploy/git"
    And I stub form submission for the current page

    # Select a content-build branch.
    And I select the "Select a different content-build branch/pull request" radio button
    And I fill in autocomplete field with selector "#edit-content-build-git-ref" with value "BRANCH main (main)"

    # Select a vets-website branch.
    And I select the "Select a different vets-website branch/pull request" radio button
    And I fill in autocomplete field with selector "#edit-vets-website-git-ref" with value "BRANCH main (main)"

    When I click the "Release content" button
    And I wait for form submission
    Then I should see "Content release requested successfully"
    And I should see "Build request skipped; form is under test."
    And I should see "Set content_build version skipped; form is under test."
    And I should see "Set vets_website version skipped; form is under test."

  @new_content_release @critical_path
  Scenario: The Simple content release form should not display the content release status block.
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line
    When I am at "/admin/content/deploy/simple"
    And I reload the page
    And I scroll to position "bottom"
    Then "Content release status" should not exist

  @new_content_release @critical_path
  Scenario: The Git content release form should normally display no in-process releases by default.
    Given I am logged in as a user with the "content_admin" role
    And I reset the content release state from the command line
    When I am at "/admin/content/deploy/git"
    And I reload the page
    And I scroll to position "bottom"
    Then "Ready" should exist
    # This is only on local/Tugboat ATM. I should add it back later.
    # And I should see "View the full output of the last completed build process (including a broken link report)."
