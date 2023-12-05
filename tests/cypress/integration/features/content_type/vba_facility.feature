@content_type__vba_facility
Feature: CMS User may effectively interact with the VBA Facility form
  In order to confirm that cms user have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Log in and try to edit an archived VBA Facility as a VBA editor
    When I am logged in as a user with the roles "content_creator_vba, content_publisher"
    And my workbench access sections are set to "1065"
    Then I am at "/node/4071/"
    Then the primary tab "View" should exist
    Then the primary tab "Edit" should not exist
