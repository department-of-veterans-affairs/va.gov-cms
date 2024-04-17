@jsonapi_explorer
Feature: JSON:API Explorer Tests

  Scenario: Test JSON:API Explorer navigation and filtering
    Given I am logged in as a user with the "content_api_consumer" role
    And I am at "/admin/config/services/openapi"
    And I run the drush command "cr"
    Then only one JSON:API Explorer link should be visible
    When I click "Explore with Swagger UI"
    Then the element with selector "h2.title" should contain "VA.gov CMS - JSON API"
    And the JSON:API Explorer tag sections should be collapsed by default and expandable
    When I fill in field with selector "input.operation-filter-input" with value "Block type"
    Then only the "Block type" tag should be visible

  Scenario: Test JSON:API Explorer deep linking and live response
    Given I am logged in as a user with the "content_api_consumer" role
    When I visit "/admin/config/services/openapi/swagger/va_gov_json_api#/Content%20-%20Full%20Width%20Alert/get_node_banner"
    Then the element with selector "h3[data-tag='Content - Full Width Alert']" should have attribute "data-is-open" with value "true"
    When I click try it out for the "get_node_banner" endpoint of the "Content - Full Width Alert" tag
    And I click "Execute"
    Then the live response status code for the "get_node_banner" endpoint of the "Content - Full Width Alert" tag should be "200"
