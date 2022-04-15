Feature: Content Release
  In order to reliably and predictably release content
  As anyone involved in the project
  I need the content release page to manage and reflect an orderly underlying release process

  Scenario: The content release page should normally display no in-process releases
    Given I am logged in as a user with the "content_admin" role
    And I clear the web build queue
    And I am at "/admin/content/deploy"
    Then I should see "No recent updates"
    Then I clear the web build queue

  Scenario: The content release page should show a pending default release initiated within the browser
    Given I am logged in as a user with the "content_admin" role
    And I clear the web build queue
    And I initiate a content release
    Then I should see "Pending"
    Then I clear the web build queue

  Scenario: The content release page should show a pending chosen release initiated within the browser
    Given I am logged in as a user with the "content_admin" role
    And I clear the web build queue
    And I initiate a content release with the branch "master"
    And I should see "Pending"
    Then I clear the web build queue

  Scenario: The content release page should show a pending default release initiated from the command line
    Given I am logged in as a user with the "content_admin" role
    And I clear the web build queue
    And I initiate a content release from the command line
    And I reload the page
    Then I should see "Pending"
    Then I clear the web build queue

  Scenario: The content release page should show a pending chosen release initiated from the command line
    Given I am logged in as a user with the "content_admin" role
    And I clear the web build queue
    And I initiate a content release from the command line with the branch "master"
    And I reload the page
    Then I should see "Branch: "
    And I should see "Pending"
    Then I clear the web build queue

  Scenario: Save nodes and see changes in the CMS and the front end website. Confirm cms menu items are in static site build.
    Given I am logged in as a user with the "administrator" role

    # Test content editing and publishing.
    And I am at "node/2/edit"
    And I fill in "Page title" with "VA blind and low vision rehabilitation services - EDITED"
    And I select option "Published" from dropdown "Save as"
    And I fill in "Revision log message" with "Test publishing"
    And I click the button with selector "input#edit-submit"
    Then I should see "VA blind and low vision rehabilitation services - EDITED has been updated."
    And the element with selector ".view-right-sidebar-latest-revision" should contain "Recent changes"

    # Build the static site.
    When I trigger a content release
    # And I am at "/static/"
    # And the element with selector "h1.homepage-heading" should contain "Access and manage your VA benefits and health care"
    And I am at "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services - EDITED"

    # Test preview functionality.
    And I click the "Preview" button
    Then I should not see "Static content file does not yet exist"
    And I should see "VA blind and low vision rehabilitation services - EDITED"
    And I should not see an element with the selector ".view-right-sidebar-latest-revision"
    And the path should equal "/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"

    # Test direct access to static pages.
    And I am at "/static/health-care/about-va-health-benefits/vision-care/blind-low-vision-rehab-services"
    Then I should see "VA blind and low vision rehabilitation services - EDITED"
