@content_editing
Feature: CMS Users see the EWA block.
  In order to confirm that cms users have access to the necessary functionality
  As anyone involved in the project
  I need to have certain functionality available

  Scenario: Confirm that the EWA block URL is shown correctly.
    Given I am logged in as a user with the "administrator" role
    When I create a 'office' node

    # Confirm that the va.gov url is not shown for nodes without a published revision.
    Then the element with selector "#block-entitymetadisplay" should contain "Content Type: Office"
    And the element with selector "#block-entitymetadisplay" should not contain "VA.gov URL"

    # Confirm that the va.gov url is shown for nodes with a published revision.
    When I publish the node
    Then the element with selector ".view-right-sidebar-latest-revision" should contain "Published"
    And the element with selector "#block-entitymetadisplay" should contain "VA.gov URL"
    And the element with selector "#block-entitymetadisplay" should contain "(pending)"
    # Test that the markup is not escaped inappropriately.
    And the element with selector "#block-entitymetadisplay" should not contain "</span>"
