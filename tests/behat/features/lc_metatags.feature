@api
Feature: Learning Center metatags are correct
  In order to confirm that learning center metatags is correct
  As anyone involved in the project
  I need to see the correct metatags for LC node types

Background:
  Given "checklist" content:
    | title                     | status  |
    | Test Checklist            |       1 |
  Given "faq_multiple_q_a" content:
    | title                     | status  |
    | Test Multiple Q&A         |       1 |
  Given "media_list_images" content:
    | title                     | status  |
    | Test Media Images         |       1 |
  Given "media_list_videos" content:
    | title                     | status  |
    | Test Media Videos         |       1 |
  Given "q_a" content:
    | title                     | status  |
    | Test Q&A                  |       1 |
  Given "step_by_step" content:
    | title                     | status  |
    | Test Step by Step         |       1 |
  Given "support_resources_detail_page" content:
    | title                     | status  |
    | Test Detail Page          |       1 |

@lc_metatags
  Scenario: Confirm correct metatitle for checklist nodes
    When I am logged in as a user with the "administrator" role
    And I visit the "" page for a node with the title "Test Checklist"
    Then the page title should be "Test Checklist | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Multiple Q&A"
    Then the page title should be "Test Multiple Q&A | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Media Images"
    Then the page title should be "Test Media Images | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Media Videos"
    Then the page title should be "Test Media Videos | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Q&A"
    Then the page title should be "Test Q&A | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Step by Step"
    Then the page title should be "Test Step by Step | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Detail Page"
    Then the page title should be "Test Detail Page | Veterans Affairs"