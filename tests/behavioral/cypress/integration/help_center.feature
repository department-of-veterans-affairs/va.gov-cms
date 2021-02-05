Feature: CMS Users are able to get help and/or training information in the CMS
  In order to confirm that cms users have access to the CMS help center
  As anyone involved in the project
  I need to have certain functionality available

  @help_center @jsd_widget
  Scenario: Anonymous users, who may have issues logging in, are able to submit a help desk request to JSD
    Given I am at "/user/login"
    Then I should see the JSD widget

  @help_center @jsd_widget
  Scenario: Users who are denied access to a page are able to submit a help desk request to JSD
    Given I am logged in as a user with the "content_publisher" role
    And I attempt to visit "/admin/reports/status"
    Then I should see the JSD widget

  @help_center @jsd_widget
  Scenario: Users who are not denied access to a page should not see the JSD widget
    Given I am logged in as a user with the "administrator" role
    And I am at "/admin/reports/status"
    Then I should not see the JSD widget

  @help_center @whats_new_in_the_cms_block
  Scenario: CMS Help Pages should have a visible What's New in the CMS block.
    Given I am logged in as a user with the "content_admin" role
    And I create a "documentation_page" node
    Then I should see the what's new in the CMS block

  @help_center @whats_new_in_the_cms_block
  Scenario: The CMS Announcements page should not have a visible What's New in the CMS block.
    Given I am logged in as a user with the "content_admin" role
    And I am at "/help/support/release-notes"
    Then I should not see the what's new in the CMS block

  @help_center @whats_new_in_the_cms_block
  Scenario: Node edit forms should not have a visible What's New in the CMS block.
    Given I am logged in as a user with the "content_admin" role
    And I create a "documentation_page" node
    And I click the "Edit" link
    Then I should not see the what's new in the CMS block
