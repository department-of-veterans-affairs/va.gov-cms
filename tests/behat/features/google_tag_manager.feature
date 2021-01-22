@api @d8 @google_tag_manager
Feature: Google Tag Manager dataLayer values are correct
  In order to confirm that Google Tag Manager data is recorded properly
  As anyone involved in the project
  I need to see the correct dataLayer values

  @user
  Scenario Outline: Google Tag Manager should be provided with an appropriate list of roles for the current user.
    Given I am logged in as a user with the <role> role
    And I am on "/user"
    Then the GTM data layer value for "userRoles" should be set to <roles>
    Examples:
    | role                | roles                                   |
    | "authenticated"     | '["authenticated"]'                     |
    | "content_admin"     | '["authenticated","content_admin"]'     |
    | "content_publisher" | '["authenticated","content_publisher"]' |
    | "administrator"     | '["authenticated","administrator"]'     |

  @user
  Scenario Outline: Google Tag Manager should be provided with an appropriate hashed UID for the current user.
    Given I am logged in as a user with the <role> role
    And I am on "/user"
    Then the GTM data layer user id should be correctly hashed
    Examples:
    | role                |
    | "authenticated"     |
    | "content_admin"     |
    | "content_publisher" |
    | "administrator"     |

  @user
  Scenario: Google Tag Manager should indicate anonymous users.
    Given I am an anonymous user
    And I am on "/node/2"
    Then the GTM data layer value for "userRoles" should be set to '["anonymous"]'
    And the GTM data layer value for "userId" should be unset

  Scenario: Google Tag Manager should be provided with appropriate dataLayer values for a given node.
    Given I am logged in as a user with the "content_admin" role
    And I am at "node/add/office"
    Then I should see "Create Office"

    # Create an office node with a menu link.
    And I fill in "Name" with "Test Office - BeHaT"
    And I fill in "Meta title tag" with "Test Office - BeHaT | Veterans Affairs"
    And I fill in "Owner" with "7"
    And I check "Provide a menu link"
    And I fill in "Menu link title" with "Test Office - BeHat"
    And I uncheck "Enable in menu"
    And I press "Save"
    Then I should see "Test Office - BeHaT"
    And the GTM data layer value for "contentTitle" should be set to "Test Office - BeHaT"
    And the GTM data layer value for "pageTitle" should be set to "Test Office - BeHaT"
    And the GTM data layer value for "contentType" should be set to "office"
    And the GTM data layer value for "nodeID" should be set
    And the GTM data layer value for "contentOwner" should be set to "Outreach Hub"
    And the GTM data layer value for "pagePath" should be set

  Scenario: An authenticated user should have no real GTM userSections value.
    Given I am logged in as a user with the "content_creator_benefits_hubs" role
    And I am on "/node/2"
    Then the GTM data layer value for "userSections" should be unset

  Scenario: A content_admin user should have a GTM userSections value of all.
    Given I am logged in as a user with the "content_admin" role
    And I am on "/node/2"
    Then the GTM data layer value for "userSections" should be set to "all"

  Scenario: A user should have GTM userSections values corresponding to their currently set sections.
    Given I am logged in as a user with the "content_creator_benefits_hubs" role
    And my workbench access sections are not set
    And I am on "/node/2"
    Then the GTM data layer value for "userSections" should be unset
    And my workbench access sections are set to "administration"
    And I am on "/node/2"
    Then the GTM data layer value for "userSections" should be set to "all"
    And my workbench access sections are set to "165"
    And I am on "/node/2"
    Then the GTM data layer value for "userSections" should be set to "165"
    And my workbench access sections are set to "165,176,177,212,246,374,375,376,377,378"
    And I am on "/node/2"
    Then the GTM data layer value for "userSections" should be set to "165,176,177,212,246,374,375,376,377,378"
    And my workbench access sections are set to "1,10,157,162,163,191,192,2,204,205,206,207,208,209,210,211,247,248,249,250,251,252,253,254,255,295,3,46,5,6,65,66,67,68,69,7,70,72,73,74,75,76,77,8"
    And I am on "/node/2"
    Then the GTM data layer value for "userSections" should be set to "1,10,157,162,163,191,192,2,204,205,206,207,208,209,210,211,247,248,249,250,251,252,253,254,255,295,3,46,5,6,65,66,67,68,69,7,70,72,73,74,75,76,77,8"
    And my workbench access sections are set to "1,10,157,162,163,182,185,191,192,2,204,205,206,207,208,209,210,211,247,248,249,250,251,252,253,254,255,295,3,46,5,6,65,66,67,68,69,7,70,72,73,74,75,76,8"
    And I am on "/node/2"
    Then the GTM data layer value for "userSections" should be set to "1,10,157,162,163,182,185,191,192,2,204,205,206,207,208,209,210,211,247,248,249,250,251,252,253,254,255,295,3,46,5,6,65,66,67,68,69,7,70,72,73,74,75,76"
