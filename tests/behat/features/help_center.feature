@api @help_center
Feature: CMS Users are able to get help and/or training information in the CMS
  In order to confirm that cms users have access to the CMS help center
  As anyone involved in the project
  I need to have certain functionality available

  @jsd_widget
  Scenario: Anonymous users, who may have issues logging in, are able to submit a help desk request to JSD
    Given I am an anonymous user
    And I am at "/user/login"
    And I wait 2 seconds
    Then I should see "Contact help desk"
    When I click on the text "Contact help desk"
    Then I should see "How can we help?"
