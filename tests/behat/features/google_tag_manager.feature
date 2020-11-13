@api @d8 @google_tag_manager
Feature: Google Tag Manager dataLayer values are correct
  In order to confirm that Google Tag Manager data is recorded properly
  As anyone involved in the project
  I need to see the correct dataLayer values

  @user
  Scenario Outline: Google Tag Manager should be provided with an appropriate list of roles for the current user.
    Given I am logged in as a user with the <role> role
    And I am on "/user"
    Then google tag manager data layer value for "userRoles" should be <roles>
    Examples:
    | role                | roles                                   |
    | "authenticated"     | '["authenticated"]'                     | 
    | "content_admin"     | '["authenticated","content_admin"]'     | 
    | "content_publisher" | '["authenticated","content_publisher"]' |
    | "administrator"     | '["authenticated","administrator"]'     |

  @user
  Scenario: Google Tag Manager should indicate anonymous users.
    Given I am an anonymous user
    And I am on "/node/2"
    Then google tag manager data layer value for "userRoles" should be '["anonymous"]'
