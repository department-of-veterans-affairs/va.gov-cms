Feature: CMS Users are able to get help and/or training information in the CMS
  In order to confirm that cms users have access to the CMS help center
  As anyone involved in the project
  I need to have certain functionality available

  @jsd_widget
  Scenario: Anonymous users, who may have issues logging in, are able to submit a help desk request to JSD
    Given I am at "/user/login"
    Then I should see "contact help desk"
    Then the URL for the link with text "help desk" should contain "https://va-gov.atlassian.net"

