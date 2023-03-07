Feature: Various navigation features
  In order to effectively review, edit, and publish content
  As anyone involved in the project
  I need to be able to navigate the site

  Scenario: Confirm that clicking links scrolls the page as expected
    Given I am logged in as a user with the "administrator" role
    # Any sufficiently long page with fragments pointing to sections should work.
    When I am at "/help/vet-centers/vet-center-website-development-status"
    And I scroll to xpath '//*[@id="block-vagovclaro-content"]/article'
    Then I should see "Jump to:"
    When I click the link with xpath '//*[@id="block-vagovclaro-content"]/article' containing "District 5"
    Then I should see "July 14th and 15th, 2021"
