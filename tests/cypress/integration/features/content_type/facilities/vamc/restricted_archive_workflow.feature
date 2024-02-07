Feature: VAMC System Level pages follow a Restricted Archive Workflow.

  Scenario: Content Editors cannot archive VA Police pages
    Given I am logged in as a user with the "content_editor" role
    And my workbench access sections are set to "318"
    # VAMC System Police page VA police for VA Orlando health care
    When I am at "/node/63901/edit"
    And I scroll to element "select#edit-moderation-state-0-state"
    Then an option with the text "Archived" from dropdown with selector "select#edit-moderation-state-0-state" should not be visible
    And I scroll to position "bottom"
    And I click the "Unlock" link
    And I click the "Confirm break lock" button

  Scenario: Content Admins are able to archive VA Police pages
    Given I am logged in as a user with the "content_admin" role
    When I am at "/node/63901/edit"
    And I scroll to element "select#edit-moderation-state-0-state"
    Then an option with the text "Archived" from dropdown with selector "select#edit-moderation-state-0-state" should be visible

    When I select option "Archived" from dropdown with selector "select#edit-moderation-state-0-state"
    And I fill in field with selector "#edit-revision-log-0-value" with value "[Test Data] Revision log message."
    And I save the node
    Then I should see "has been updated."
