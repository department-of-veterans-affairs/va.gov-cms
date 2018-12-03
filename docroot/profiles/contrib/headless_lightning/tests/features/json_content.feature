@api @headless
Feature: Links to content should point to JSON Content, not rendered HTML.

  @3af3400a
  Scenario Outline: Entity edit pages should provide links to the JSON representation.
    Given I am logged in as a user with the "administrator" role
    And node entities:
      | type | title          | moderation_state | path  |
      | page | Page 3af3400a  | draft            | /page3af3400a |
    And media entities:
      | bundle    | name           | embed_code                                                  | status | path   |
      | tweet     | Tweet 3af3400a | https://twitter.com/50NerdsofGrey/status/757319527151636480 | 1      | /tweet3af3400a |
    When I visit "/admin/content/<type>"
    And I edit the item named "<title>"
    Then I should see "View JSON"

    Examples:
      | type        | title          |
      |             | Page 3af3400a  |
      | media-table | Tweet 3af3400a |

  @43e31c96 @issue-#2795279
  Scenario Outline: Entity edit pages should not show links to Latest Revision when unpublished edits are present.
    Given I am logged in as a user with the "administrator" role
    And node entities:
      | type | title          | moderation_state | path  |
      | page | Page 43e31c96  | published            | /page43e31c96 |
    When I visit "/admin/content/<type>"
    And I edit the item named "<title>"
    And I select "draft" from "moderation_state[0][state]"
    And I press "Save"
    And I edit the item named "<title>"
    Then I should not see a "Latest version" link

    Examples:
      | type  | title          |
      |       | Page 43e31c96  |
