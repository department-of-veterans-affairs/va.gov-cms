Feature: Content Release
  In order to reliably and predictably release content
  As anyone involved in the project
  I need the content release page to manage and reflect an orderly underlying release process

  @content_release @content_release_page
  Scenario: The content release page should normally display no in-process releases
    Given I am logged in as a user with the "content_admin" role
    And I am at "/admin/content/deploy"
    Then I should see "No recent updates"
