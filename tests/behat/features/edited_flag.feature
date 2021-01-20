@api @edited_flag
Feature: The Edited flag indicates that a user has edited a piece of content at least once.
  In order to receive notifications about content I have edited
  As a content editor
  I need to reliably set the edited flag on content I edit

  Scenario: Content should set the edited flag when created and when edited.
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/office"
    Then I should see "Create Office"

    And I fill in "Name" with "Behat Edited Flag Test"
    And I fill in "Meta title tag" with "Just a Test | Veterans Affairs"
    And I fill in "Owner" with "7"
    And I press "Save"
    Then I should see "Behat Edited Flag Test"
    And the "edited" flag for node "Behat Edited Flag Test" should be set for me

    Then I am logged in as a user with the "content_admin" role
    Then I visit the "edit" page for a node with the title "Behat Edited Flag Test"
    And I fill in "Name" with "Behat Edited Flag Test Part Deux"
    And I press "Save"
    Then I should see "Behat Edited Flag Test Part Deux"
    And the "edited" flag for node "Behat Edited Flag Test Part Deux" should be set for me


