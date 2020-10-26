@api @d8 @google_tag_manager
Feature: Google Tag Manager dataLayer values are correct
  In order to confirm that Google Tag Manager data is recorded properly
  As anyone involved in the project
  I need to see the correct dataLayer values

  @user @perms
  Scenario: Users should see an appropriate list of roles in the dataLayer.
    Given I am logged in as a user with the "content_admin" role
    And I am on "/user"
    Then google tag manager id is "GTM-WQ3DLLB"
    Then google tag manager data layer value for "currentUserRoles" should be "authenticated,content_admin"
