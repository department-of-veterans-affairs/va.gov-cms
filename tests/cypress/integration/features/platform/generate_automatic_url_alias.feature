@content_editing @generate_automatic_url_alias
Feature: Generate automatic URL alias

  Scenario: Confirm Generate automatic URL alias is unchecked after node publish.
    When I am logged in as a user with the "administrator" role
    And I am at "node/add/landing_page"
    Then the "Generate automatic URL alias" checkbox should be checked
    And I create a "landing_page" node
    And I edit the node
    Then the "Generate automatic URL alias" checkbox should be checked
    And I publish the node
    And I edit the node
    Then the "Generate automatic URL alias" checkbox should not be checked

  Scenario: Confirm Generate automatic URL alias is unchecked after taxonomy term publish.
    Given I am logged in as a user with the "administrator" role
    And I am at "admin/structure/taxonomy/manage/health_care_service_taxonomy/add"
    Then the "Generate automatic URL alias" checkbox should be checked
    And I create a "health_care_service_taxonomy" taxonomy term
    And I edit the term
    And the "Generate automatic URL alias" checkbox should not be checked
