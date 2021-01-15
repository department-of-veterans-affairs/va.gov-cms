@api @cms_whats_new
Feature: The What's New in the CMS Block
  In order to keep CMS users apprised of recent changes and improvements in the CMS
  As anyone involved in the project
  I need to have an accessible and intuitive method of accessing a summary of the latest changes

  Scenario: CMS Help Pages should have a visible What's New in the CMS block.
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/documentation_page"
    And I fill in "Page title" with "Behat CMS Block Test"
    And I fill in "Page introduction" with "Lorem ipsem sic dolor amet et cetera ad nauseam"
    And I press "Save"
    Then the "#block-whatsnewinthecms" element should exist

  Scenario: The CMS Announcements page should not have a visible What's New in the CMS block.
    Given I am logged in as a user with the "content_admin" role
    And I am at "/help/support/release-notes"
    And the "#block-whatsnewinthecms" element should not exist
