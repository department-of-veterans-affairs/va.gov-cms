Feature: CMS Users are able to get help and/or training information in the CMS
  In order to confirm that cms users have access to the CMS help center
  As anyone involved in the project
  I need to have certain functionality available

  @jsd_widget
  Scenario: Anonymous users, who may have issues logging in, are able to submit a help desk request to JSD
    Given I am at "/user/login"
    Then I should see the JSD widget

  @jsd_widget
  Scenario: Users who are denied access to a page are able to submit a help desk request to JSD
    Given I am logged in as a user with role "content_publisher"
    And I attempt to visit "/admin/reports/status"
    Then I should see the JSD widget

  @jsd_widget
  Scenario: Users who are not denied access to a page should not see the JSD widget
    Given I am logged in as a user with role "administrator"
    And I am at "/admin/reports/status"
    Then I should not see the JSD widget
