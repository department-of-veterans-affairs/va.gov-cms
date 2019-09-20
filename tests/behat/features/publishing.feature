@api
Feature: Published state is reflected in build
    In order to confirm unpublished content is not in build and published is in build
    Then I need to unpublish it in cms
    And check that it is not visible in build
    Then I need to publish it in cms
    And check that it is visible in build

  @unpublished_content
  Scenario: Unpublish health-care and confirm it is not in build
    When I am logged in as a user with the "administrator" role
    Given I set the node with title "VA health care" to "unpublished"
    When I run "cd .. && composer va:web:build"
    And I am on "/static/health-care"
    Then the response status code should be 403

  @published_content
  Scenario: Publish health-care and confirm it is in build
    When I am logged in as a user with the "administrator" role
    Given I set the node with title "VA health care" to "published"
    When I run "cd .. && composer va:web:build"
    And I am on "/static/health-care"
    Then the response status code should be 200




