@api
Feature: Learning Center metatags are correct
  In order to confirm that learning center metatags is correct
  As anyone involved in the project
  I need to see the correct metatags for LC node types

Background:
  Given "checklist" content:
    | title                     | status  |
    | Test Checklist - BeHaT    |       1 |
  Given "faq_multiple_q_a" content:
    | title                     | status  |
    | Test Multiple Q&A - BeHaT |       1 |
  Given "media_list_images" content:
    | title                     | status  |
    | Test Media Images - BeHaT |       1 |
  Given "media_list_videos" content:
    | title                     | status  |
    | Test Media Videos - BeHaT |       1 |
  Given "q_a" content:
    | title                     | status  |
    | Test Q&A - BeHaT          |       1 |
  Given "step_by_step" content:
    | title                     | status  |
    | Test Step by Step - BeHaT |       1 |
  Given "support_resources_detail_page" content:
    | title                     | status  |
    | Test Detail Page - BeHaT  |       1 |

@lc_metatags
  Scenario: Confirm correct metatitle for checklist nodes
    When I am logged in as a user with the "administrator" role
    And I visit the "" page for a node with the title "Test Checklist - BeHaT"
    Then the page title should be "Test Checklist - BeHaT | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Multiple Q&A - BeHaT"
    Then the page title should be "Test Multiple Q&A - BeHaT | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Media Images - BeHaT"
    Then the page title should be "Test Media Images - BeHaT | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Media Videos - BeHaT"
    Then the page title should be "Test Media Videos - BeHaT | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Q&A - BeHaT"
    Then the page title should be "Test Q&A - BeHaT | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Step by Step - BeHaT"
    Then the page title should be "Test Step by Step - BeHaT | Veterans Affairs"
    And I visit the "" page for a node with the title "Test Detail Page - BeHaT"
    Then the page title should be "Test Detail Page - BeHaT | Veterans Affairs"